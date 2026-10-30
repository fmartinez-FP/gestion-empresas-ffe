<?php

namespace Tests\Feature;

use App\Models\Colocacion;
use App\Models\CicloFormativo;
use App\Models\Empresa;
use App\Models\User;
use App\Services\Ldap\DirectorioLdap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

/**
 * Usuarios alineados con LDAP: bajas, borrados y rol de administrador.
 */
class SincronizacionLdapTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['rol' => 'admin', 'activo' => true, 'username' => 'admin']);
    }

    private function directorio(array $cuentas): void
    {
        $base = ['admin' => ['activo' => true, 'en_ffe' => true, 'admin' => true]];
        $this->mock(DirectorioLdap::class)
            ->shouldReceive('usuarios')->andReturn($cuentas + $base)
            ->shouldReceive('usuario')->andReturnUsing(fn ($uid) => ($cuentas + $base)[mb_strtolower($uid)] ?? null);
    }

    private function cuenta(bool $activo = true, bool $enFfe = true, bool $admin = false): array
    {
        return ['activo' => $activo, 'en_ffe' => $enFfe, 'admin' => $admin];
    }

    private function profesor(string $username, array $extra = []): User
    {
        return User::factory()->create(['rol' => 'profesor', 'activo' => true, 'username' => $username] + $extra);
    }

    #[Test]
    public function un_usuario_desactivado_en_ldap_queda_inactivo_y_no_se_borra(): void
    {
        $baja = $this->profesor('baja');
        $this->directorio(['baja' => $this->cuenta(activo: false)]);

        Artisan::call('ffe:sincronizar-ldap');

        $this->assertFalse($baja->fresh()->activo);
    }

    #[Test]
    public function un_usuario_fuera_del_grupo_de_la_aplicacion_queda_inactivo(): void
    {
        $fuera = $this->profesor('fuera');
        $this->directorio(['fuera' => $this->cuenta(enFfe: false)]);

        Artisan::call('ffe:sincronizar-ldap');

        $this->assertFalse($fuera->fresh()->activo);
    }

    #[Test]
    public function un_usuario_que_vuelve_a_estar_activo_en_ldap_se_reactiva(): void
    {
        $vuelve = $this->profesor('vuelve', ['activo' => false]);
        $this->directorio(['vuelve' => $this->cuenta()]);

        Artisan::call('ffe:sincronizar-ldap');

        $this->assertTrue($vuelve->fresh()->activo);
    }

    #[Test]
    public function un_usuario_borrado_de_ldap_se_borra_de_la_aplicacion(): void
    {
        $borrado = $this->profesor('borrado');
        $this->directorio([]);

        Artisan::call('ffe:sincronizar-ldap');

        $this->assertDatabaseMissing('users', ['id' => $borrado->id]);
        $this->assertDatabaseHas('auditoria', ['modelo' => 'User', 'modelo_id' => $borrado->id, 'accion' => 'sincronizacion_ldap']);
    }

    #[Test]
    public function un_usuario_borrado_de_ldap_con_datos_asociados_se_conserva_inactivo(): void
    {
        $borrado = $this->profesor('conDatos');
        Empresa::factory()->create(['creador_id' => $borrado->id]);
        $this->directorio([]);

        Artisan::call('ffe:sincronizar-ldap');

        $this->assertDatabaseHas('users', ['id' => $borrado->id, 'activo' => false]);
        $this->assertDatabaseCount('empresas', 1);
    }

    #[Test]
    public function los_alumnos_y_tutores_de_empresa_no_se_tocan(): void
    {
        $alumno = User::factory()->create(['rol' => 'alumno', 'activo' => true]);
        $tutor = User::factory()->create(['rol' => 'tutor_empresa', 'activo' => true]);
        $this->directorio([]);

        Artisan::call('ffe:sincronizar-ldap');

        $this->assertDatabaseHas('users', ['id' => $alumno->id, 'activo' => true]);
        $this->assertDatabaseHas('users', ['id' => $tutor->id, 'activo' => true]);
    }

    #[Test]
    public function simular_no_cambia_nada(): void
    {
        $borrado = $this->profesor('borrado');
        $baja = $this->profesor('baja');
        $this->directorio(['baja' => $this->cuenta(activo: false)]);

        Artisan::call('ffe:sincronizar-ldap', ['--simular' => true]);

        $this->assertDatabaseHas('users', ['id' => $borrado->id]);
        $this->assertTrue($baja->fresh()->activo);
    }

    #[Test]
    public function si_el_directorio_no_devuelve_cuentas_no_se_toca_nada(): void
    {
        $profesor = $this->profesor('profe');
        $this->mock(DirectorioLdap::class)->shouldReceive('usuarios')->andReturn([]);

        $this->assertSame(1, Artisan::call('ffe:sincronizar-ldap'));
        $this->assertDatabaseHas('users', ['id' => $profesor->id, 'activo' => true]);
    }

    #[Test]
    public function si_el_directorio_falla_no_se_toca_nada(): void
    {
        $profesor = $this->profesor('profe');
        $this->mock(DirectorioLdap::class)->shouldReceive('usuarios')->andThrow(new RuntimeException('caído'));

        $this->assertSame(1, Artisan::call('ffe:sincronizar-ldap'));
        $this->assertDatabaseHas('users', ['id' => $profesor->id]);
    }

    #[Test]
    public function si_el_grupo_de_administradores_viene_vacio_no_se_toca_nada(): void
    {
        $profesor = $this->profesor('profe');
        $this->mock(DirectorioLdap::class)->shouldReceive('usuarios')->andReturn([
            'admin' => $this->cuenta(), 'profe' => $this->cuenta(activo: false),
        ]);

        $this->assertSame(1, Artisan::call('ffe:sincronizar-ldap'));
        $this->assertTrue($profesor->fresh()->activo);
    }

    // ---- Rol de administrador ------------------------------------------------

    #[Test]
    public function un_miembro_del_grupo_admins_pasa_a_ser_administrador(): void
    {
        $nuevo = $this->profesor('nuevo');
        $this->directorio(['nuevo' => $this->cuenta(admin: true)]);

        Artisan::call('ffe:sincronizar-ldap');

        $this->assertSame('admin', $nuevo->fresh()->rol);
    }

    #[Test]
    public function quien_sale_del_grupo_admins_pasa_a_profesor(): void
    {
        $otro = User::factory()->create(['rol' => 'admin', 'activo' => true, 'username' => 'otro']);
        $this->directorio(['otro' => $this->cuenta(admin: false)]);

        Artisan::call('ffe:sincronizar-ldap');

        $this->assertSame('profesor', $otro->fresh()->rol);
        $this->assertSame('admin', $this->admin->fresh()->rol);
    }

    #[Test]
    public function nunca_se_deja_la_aplicacion_sin_administrador(): void
    {
        // El único administrador de la app ya no está en admins, pero otro (aún sin ficha) sí.
        $this->mock(DirectorioLdap::class)->shouldReceive('usuarios')->andReturn([
            'admin' => $this->cuenta(admin: false), 'recien' => $this->cuenta(admin: true),
        ]);

        Artisan::call('ffe:sincronizar-ldap');

        $this->assertSame('admin', $this->admin->fresh()->rol);
        $this->assertTrue($this->admin->fresh()->activo);
    }

    #[Test]
    public function el_login_concede_el_rol_admin_segun_el_grupo_ldap(): void
    {
        $nuevo = $this->profesor('nuevo');
        $this->directorio(['nuevo' => $this->cuenta(admin: true)]);

        app(\App\Services\Ldap\SincronizacionLdapService::class)->refrescarRol($nuevo);

        $this->assertSame('admin', $nuevo->fresh()->rol);
    }

    #[Test]
    public function un_usuario_nuevo_nace_profesor_aunque_se_llame_admin(): void
    {
        $nuevo = User::create(['username' => 'admin2', 'nombre' => 'X', 'email' => 'x@example.com']);
        $this->assertSame('profesor', $nuevo->rol);
    }

    // ---- Pantalla de usuarios --------------------------------------------------

    #[Test]
    public function no_se_puede_asignar_el_rol_admin_desde_la_aplicacion(): void
    {
        $profesor = $this->profesor('profe');

        $this->actingAs($this->admin)
            ->put(route('admin.usuarios.update', $profesor), ['rol' => 'admin'])
            ->assertSessionHasErrors('rol');

        $this->assertSame('profesor', $profesor->fresh()->rol);
    }

    #[Test]
    public function no_se_puede_cambiar_el_rol_de_un_administrador_desde_la_aplicacion(): void
    {
        $otro = User::factory()->create(['rol' => 'admin', 'activo' => true]);

        $this->actingAs($this->admin)
            ->put(route('admin.usuarios.update', $otro), ['rol' => 'profesor'])
            ->assertSessionHas('error');

        $this->assertSame('admin', $otro->fresh()->rol);
    }

    #[Test]
    public function la_pantalla_de_edicion_no_ofrece_el_rol_administrador(): void
    {
        $profesor = $this->profesor('profe');

        $this->actingAs($this->admin)
            ->get(route('admin.usuarios.edit', $profesor))
            ->assertOk()
            ->assertDontSee('value="admin"', false)
            ->assertSee('value="profesor"', false);

        $this->actingAs($this->admin)
            ->get(route('admin.usuarios.edit', $this->admin))
            ->assertOk()
            ->assertDontSee('name="rol"', false)
            ->assertSee('grupo');
    }

    #[Test]
    public function el_boton_sincroniza_y_solo_lo_puede_usar_un_administrador(): void
    {
        $baja = $this->profesor('baja');
        $this->directorio(['baja' => $this->cuenta(activo: false)]);

        $this->actingAs($this->admin)
            ->post(route('admin.usuarios.sincronizar-ldap'))
            ->assertSessionHas('success');
        $this->assertFalse($baja->fresh()->activo);

        $this->actingAs($this->profesor('profe'))
            ->post(route('admin.usuarios.sincronizar-ldap'))
            ->assertForbidden();
    }
}
