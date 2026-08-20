<?php

namespace Tests\Feature;

use App\Models\Contacto;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContactoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CicloFormativoSeeder::class);
    }

    private function crearEmpresa(User $user): Empresa
    {
        return Empresa::create([
            'nombre'     => 'Empresa Test S.L.',
            'cif'        => 'B' . rand(10000000, 99999999),
            'creador_id' => $user->id,
        ]);
    }

    private function crearContacto(Empresa $empresa, array $overrides = []): Contacto
    {
        return Contacto::create(array_merge([
            'empresa_id'         => $empresa->id,
            'registrado_por_id'  => $empresa->creador_id,
            'tipo'               => 'llamada',
            'resultado'          => 'exitoso',
            'fecha_contacto'     => now(),
        ], $overrides));
    }

    private function datosContacto(array $overrides = []): array
    {
        return array_merge([
            'tipo'           => 'llamada',
            'resultado'      => 'exitoso',
            'fecha_contacto' => now()->toDateString(),
        ], $overrides);
    }

    #[Test]
    public function creador_puede_ver_formulario_crear_contacto()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($user);

        $this->actingAs($user)
            ->get("/empresas/{$empresa->id}/contactos/create")
            ->assertStatus(200);
    }

    #[Test]
    public function profesor_ajeno_no_puede_ver_formulario_crear_contacto()
    {
        $propietario = User::factory()->create(['rol' => 'profesor']);
        $otro = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($propietario);

        $this->actingAs($otro)
            ->get("/empresas/{$empresa->id}/contactos/create")
            ->assertStatus(403);
    }

    #[Test]
    public function creador_puede_crear_contacto()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($user);

        $this->actingAs($user)
            ->post("/empresas/{$empresa->id}/contactos", $this->datosContacto())
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseHas('contactos', [
            'empresa_id' => $empresa->id,
            'tipo'       => 'llamada',
            'resultado'  => 'exitoso',
        ]);
    }

    #[Test]
    public function profesor_ajeno_no_puede_crear_contacto()
    {
        $propietario = User::factory()->create(['rol' => 'profesor']);
        $otro = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($propietario);

        $this->actingAs($otro)
            ->post("/empresas/{$empresa->id}/contactos", $this->datosContacto())
            ->assertStatus(403);

        $this->assertDatabaseMissing('contactos', ['empresa_id' => $empresa->id]);
    }

    #[Test]
    public function creador_puede_ver_formulario_editar_contacto()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($user);
        $contacto = $this->crearContacto($empresa);

        $this->actingAs($user)
            ->get("/empresas/{$empresa->id}/contactos/{$contacto->id}/edit")
            ->assertStatus(200);
    }

    #[Test]
    public function profesor_ajeno_no_puede_editar_contacto()
    {
        $propietario = User::factory()->create(['rol' => 'profesor']);
        $otro = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($propietario);
        $contacto = $this->crearContacto($empresa);

        $this->actingAs($otro)
            ->get("/empresas/{$empresa->id}/contactos/{$contacto->id}/edit")
            ->assertStatus(403);

        $this->actingAs($otro)
            ->put("/empresas/{$empresa->id}/contactos/{$contacto->id}", $this->datosContacto(['resultado' => 'pendiente']))
            ->assertStatus(403);

        $this->assertDatabaseHas('contactos', ['id' => $contacto->id, 'resultado' => 'exitoso']);
    }

    #[Test]
    public function creador_puede_actualizar_contacto()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($user);
        $contacto = $this->crearContacto($empresa);

        $this->actingAs($user)
            ->put("/empresas/{$empresa->id}/contactos/{$contacto->id}", $this->datosContacto(['resultado' => 'cita_programada']))
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseHas('contactos', ['id' => $contacto->id, 'resultado' => 'cita_programada']);
    }

    #[Test]
    public function creador_puede_eliminar_contacto()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($user);
        $contacto = $this->crearContacto($empresa);

        $this->actingAs($user)
            ->delete("/empresas/{$empresa->id}/contactos/{$contacto->id}")
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseMissing('contactos', ['id' => $contacto->id]);
    }

    #[Test]
    public function profesor_ajeno_no_puede_eliminar_contacto()
    {
        $propietario = User::factory()->create(['rol' => 'profesor']);
        $otro = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($propietario);
        $contacto = $this->crearContacto($empresa);

        $this->actingAs($otro)
            ->delete("/empresas/{$empresa->id}/contactos/{$contacto->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('contactos', ['id' => $contacto->id]);
    }

    #[Test]
    public function admin_puede_gestionar_contactos_de_cualquier_empresa()
    {
        $profesor = User::factory()->create(['rol' => 'profesor']);
        $admin = User::factory()->create(['rol' => 'admin']);
        $empresa = $this->crearEmpresa($profesor);

        $this->actingAs($admin)
            ->post("/empresas/{$empresa->id}/contactos", $this->datosContacto())
            ->assertRedirect(route('empresas.show', $empresa));

        $this->assertDatabaseHas('contactos', ['empresa_id' => $empresa->id]);
    }

    #[Test]
    public function contacto_de_otra_empresa_devuelve_404()
    {
        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa1 = $this->crearEmpresa($user);
        $empresa2 = Empresa::create(['nombre' => 'Otra', 'cif' => 'B00000001', 'creador_id' => $user->id]);
        $contacto = $this->crearContacto($empresa2);

        $this->actingAs($user)
            ->put("/empresas/{$empresa1->id}/contactos/{$contacto->id}", $this->datosContacto())
            ->assertStatus(404);

        $this->actingAs($user)
            ->delete("/empresas/{$empresa1->id}/contactos/{$contacto->id}")
            ->assertStatus(404);
    }

    #[Test]
    public function creador_puede_descargar_archivo_adjunto()
    {
        Storage::fake('private');

        $user = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($user);

        $archivo = UploadedFile::fake()->create('acta.pdf', 100, 'application/pdf');

        $this->actingAs($user)->post("/empresas/{$empresa->id}/contactos", $this->datosContacto([
            'archivo' => $archivo,
        ]));

        $contacto = Contacto::where('empresa_id', $empresa->id)->firstOrFail();

        $this->actingAs($user)
            ->get("/empresas/{$empresa->id}/contactos/{$contacto->id}/archivo")
            ->assertStatus(200);
    }

    #[Test]
    public function profesor_ajeno_no_puede_descargar_archivo_adjunto()
    {
        Storage::fake('private');

        $propietario = User::factory()->create(['rol' => 'profesor']);
        $otro = User::factory()->create(['rol' => 'profesor']);
        $empresa = $this->crearEmpresa($propietario);
        $contacto = $this->crearContacto($empresa, [
            'archivo_adjunto' => 'contactos/' . $empresa->id . '/acta.pdf',
            'archivo_nombre'  => 'acta.pdf',
        ]);

        $this->actingAs($otro)
            ->get("/empresas/{$empresa->id}/contactos/{$contacto->id}/archivo")
            ->assertStatus(403);
    }
}
