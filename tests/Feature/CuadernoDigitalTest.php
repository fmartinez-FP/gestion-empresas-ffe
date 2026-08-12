<?php

namespace Tests\Feature;

use App\Models\AsignacionFct;
use App\Models\CalendarioAsignacion;
use App\Models\SeguimientoDiario;
use App\Models\TokenTutorEmpresa;
use App\Models\User;
use App\Services\CalendarioAsignacionService;
use App\Services\NoLectivoIesService;
use App\Services\TokenTutorEmpresaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CuadernoDigitalTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // TokenTutorEmpresaService
    // =========================================================================

    #[Test]
    public function token_generado_tiene_expires_at_30_dias(): void
    {
        $asignacion = AsignacionFct::factory()->create();
        $servicio   = new TokenTutorEmpresaService();

        $token = $servicio->generar($asignacion);

        $this->assertNotNull($token->token);
        $this->assertTrue($token->expires_at->greaterThan(now()->addDays(29)));
        $this->assertNull($token->usado_at);
    }

    #[Test]
    public function generar_token_invalida_token_anterior(): void
    {
        $asignacion = AsignacionFct::factory()->create();
        $servicio   = new TokenTutorEmpresaService();

        $primero  = $servicio->generar($asignacion);
        $segundo  = $servicio->generar($asignacion);

        $primero->refresh();
        $this->assertFalse($primero->estaVigente());
        $this->assertTrue($segundo->estaVigente());
    }

    #[Test]
    public function validar_retorna_token_vigente(): void
    {
        $asignacion = AsignacionFct::factory()->create();
        $servicio   = new TokenTutorEmpresaService();

        $token    = $servicio->generar($asignacion);
        $resultado = $servicio->validar($token->token);

        $this->assertNotNull($resultado);
        $this->assertEquals($token->id, $resultado->id);
    }

    #[Test]
    public function validar_retorna_null_para_token_expirado(): void
    {
        $asignacion = AsignacionFct::factory()->create();
        $token = TokenTutorEmpresa::factory()
            ->for($asignacion, 'asignacion')
            ->expirado()
            ->create();

        $servicio  = new TokenTutorEmpresaService();
        $resultado = $servicio->validar($token->token);

        $this->assertNull($resultado);
    }

    #[Test]
    public function marcar_usado_registra_ip_y_timestamp(): void
    {
        $asignacion = AsignacionFct::factory()->create();
        $servicio   = new TokenTutorEmpresaService();
        $token      = $servicio->generar($asignacion);

        $servicio->marcarUsado($token, '10.0.0.1');
        $token->refresh();

        $this->assertNotNull($token->usado_at);
        $this->assertEquals('10.0.0.1', $token->ip_uso);
    }

    #[Test]
    public function marcar_usado_no_sobreescribe_si_ya_fue_usado(): void
    {
        $asignacion = AsignacionFct::factory()->create();
        $token = TokenTutorEmpresa::factory()
            ->for($asignacion, 'asignacion')
            ->usado()
            ->create();

        $usadoAt = $token->usado_at;
        $servicio = new TokenTutorEmpresaService();
        $servicio->marcarUsado($token, '99.99.99.99');
        $token->refresh();

        $this->assertEquals($usadoAt->toDateTimeString(), $token->usado_at->toDateTimeString());
        $this->assertNotEquals('99.99.99.99', $token->ip_uso);
    }

    // =========================================================================
    // CalendarioAsignacionService
    // =========================================================================

    #[Test]
    public function dias_laborables_excluye_fines_de_semana(): void
    {
        // Lunes 2026-06-01 a viernes 2026-06-05 = 5 dias laborables
        $asignacion = AsignacionFct::factory()->create([
            'fecha_inicio' => '2026-06-01',
            'fecha_fin'    => '2026-06-05',
        ]);

        $servicio = new CalendarioAsignacionService(new NoLectivoIesService());
        $this->assertEquals(5, $servicio->diasLaborables($asignacion));
    }

    #[Test]
    public function dias_laborables_descuenta_festivos_marcados(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'fecha_inicio' => '2026-06-01',
            'fecha_fin'    => '2026-06-05',
        ]);

        CalendarioAsignacion::factory()->festivo()->create([
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-06-02',
        ]);

        $servicio = new CalendarioAsignacionService(new NoLectivoIesService());
        $this->assertEquals(4, $servicio->diasLaborables($asignacion));
    }

    #[Test]
    public function dias_laborables_retorna_cero_sin_fechas(): void
    {
        $asignacion = AsignacionFct::factory()->create([
            'fecha_inicio' => null,
            'fecha_fin'    => null,
        ]);

        $servicio = new CalendarioAsignacionService(new NoLectivoIesService());
        $this->assertEquals(0, $servicio->diasLaborables($asignacion));
    }

    #[Test]
    public function marcar_dia_crea_entrada_en_calendario(): void
    {
        $asignacion = AsignacionFct::factory()->create();
        $servicio   = new CalendarioAsignacionService(new NoLectivoIesService());

        $dia = $servicio->marcarDia($asignacion, Carbon::parse('2026-06-10'), 'festivo', 'San Antonio');

        $this->assertDatabaseHas('calendario_asignacion', [
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-06-10',
            'tipo'          => 'festivo',
            'motivo'        => 'San Antonio',
        ]);
    }

    #[Test]
    public function marcar_dia_actualiza_entrada_existente(): void
    {
        $asignacion = AsignacionFct::factory()->create();
        $servicio   = new CalendarioAsignacionService(new NoLectivoIesService());

        $servicio->marcarDia($asignacion, Carbon::parse('2026-06-10'), 'festivo', 'San Antonio');
        $servicio->marcarDia($asignacion, Carbon::parse('2026-06-10'), 'baja', 'Baja médica');

        $this->assertDatabaseCount('calendario_asignacion', 1);
        $this->assertDatabaseHas('calendario_asignacion', ['tipo' => 'baja']);
    }

    #[Test]
    public function eliminar_dia_borra_entrada(): void
    {
        $asignacion = AsignacionFct::factory()->create();
        $dia = CalendarioAsignacion::factory()->festivo()->create([
            'asignacion_id' => $asignacion->id,
        ]);

        $servicio = new CalendarioAsignacionService(new NoLectivoIesService());
        $servicio->eliminarDia($dia);

        $this->assertDatabaseMissing('calendario_asignacion', ['id' => $dia->id]);
    }

    // =========================================================================
    // Portal alumno — registro diario
    // =========================================================================

    #[Test]
    public function alumno_puede_registrar_entrada_del_dia(): void
    {
        Storage::fake('private');

        $user       = User::factory()->create(['rol' => 'alumno']);
        $asignacion = AsignacionFct::factory()->create([
            'fecha_inicio' => today()->subDays(5)->toDateString(),
            'fecha_fin'    => today()->addDays(30)->toDateString(),
            'estado'       => 'activa',
        ]);
        $asignacion->alumno->update(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'web_externo')->post(route('portal.cuaderno.store'), [
            'descripcion_tareas' => 'Tareas de prueba en la empresa.',
            'hora_entrada'       => '09:00',
            'hora_salida'        => '14:00',
        ]);

        $response->assertRedirect(route('portal.cuaderno.index'));
        $this->assertDatabaseHas('seguimiento_diario', [
            'asignacion_id'      => $asignacion->id,
            'fecha'              => today()->toDateString(),
            'descripcion_tareas' => 'Tareas de prueba en la empresa.',
        ]);
    }

    #[Test]
    public function alumno_no_puede_registrar_dos_entradas_el_mismo_dia(): void
    {
        $user       = User::factory()->create(['rol' => 'alumno']);
        $asignacion = AsignacionFct::factory()->create([
            'fecha_inicio' => today()->subDays(5)->toDateString(),
            'fecha_fin'    => today()->addDays(30)->toDateString(),
            'estado'       => 'activa',
        ]);
        $asignacion->alumno->update(['user_id' => $user->id]);

        SeguimientoDiario::factory()->create([
            'asignacion_id' => $asignacion->id,
            'fecha'         => today()->toDateString(),
        ]);

        $response = $this->actingAs($user, 'web_externo')->post(route('portal.cuaderno.store'), [
            'descripcion_tareas' => 'Segunda entrada.',
            'hora_entrada'       => '09:00',
            'hora_salida'        => '14:00',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function alumno_no_puede_registrar_entrada_con_asignacion_inactiva(): void
    {
        $user       = User::factory()->create(['rol' => 'alumno']);
        $asignacion = AsignacionFct::factory()->create([
            'fecha_inicio' => today()->subDays(30)->toDateString(),
            'fecha_fin'    => today()->subDays(1)->toDateString(),
            'estado'       => 'finalizada',
        ]);
        $asignacion->alumno->update(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'web_externo')->post(route('portal.cuaderno.store'), [
            'descripcion_tareas' => 'Intento.',
            'hora_entrada'       => '09:00',
            'hora_salida'        => '14:00',
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function alumno_puede_editar_entrada_de_hoy_no_confirmada(): void
    {
        $user       = User::factory()->create(['rol' => 'alumno']);
        $asignacion = AsignacionFct::factory()->create([
            'fecha_inicio' => today()->subDays(5)->toDateString(),
            'fecha_fin'    => today()->addDays(30)->toDateString(),
            'estado'       => 'activa',
        ]);
        $asignacion->alumno->update(['user_id' => $user->id]);

        $seguimiento = SeguimientoDiario::factory()->create([
            'asignacion_id'    => $asignacion->id,
            'fecha'            => today()->toDateString(),
            'confirmado_tutor' => false,
        ]);

        $response = $this->actingAs($user, 'web_externo')
            ->put(route('portal.cuaderno.update', $seguimiento), [
                'descripcion_tareas' => 'Descripción actualizada.',
                'hora_entrada'       => '08:30',
                'hora_salida'        => '13:30',
            ]);

        $response->assertRedirect(route('portal.cuaderno.index'));
        $this->assertDatabaseHas('seguimiento_diario', [
            'id'                 => $seguimiento->id,
            'descripcion_tareas' => 'Descripción actualizada.',
        ]);
    }

    #[Test]
    public function alumno_no_puede_editar_entrada_confirmada(): void
    {
        $user       = User::factory()->create(['rol' => 'alumno']);
        $asignacion = AsignacionFct::factory()->create([
            'fecha_inicio' => today()->subDays(5)->toDateString(),
            'fecha_fin'    => today()->addDays(30)->toDateString(),
            'estado'       => 'activa',
        ]);
        $asignacion->alumno->update(['user_id' => $user->id]);

        $seguimiento = SeguimientoDiario::factory()->confirmado()->create([
            'asignacion_id' => $asignacion->id,
            'fecha'         => today()->toDateString(),
        ]);

        $response = $this->actingAs($user, 'web_externo')
            ->put(route('portal.cuaderno.update', $seguimiento), [
                'descripcion_tareas' => 'Intento editar confirmada.',
                'hora_entrada'       => '09:00',
                'hora_salida'        => '14:00',
            ]);

        $response->assertStatus(403);
    }

    // =========================================================================
    // Confirmación tutor IES
    // =========================================================================

    #[Test]
    public function tutor_ies_puede_confirmar_seguimiento(): void
    {
        $tutor      = User::factory()->create(['rol' => 'profesor']);
        $asignacion = AsignacionFct::factory()->create([
            'tutor_ies_id' => $tutor->id,
            'estado'       => 'activa',
        ]);
        $seguimiento = SeguimientoDiario::factory()->create([
            'asignacion_id'    => $asignacion->id,
            'confirmado_tutor' => false,
        ]);

        $response = $this->actingAs($tutor)->post(
            route('asignaciones.seguimientos.confirmar', [$asignacion, $seguimiento]),
            ['comentario_tutor' => 'Todo correcto.']
        );

        $response->assertRedirect();
        $seguimiento->refresh();
        $this->assertTrue($seguimiento->confirmado_tutor);
        $this->assertEquals('Todo correcto.', $seguimiento->comentario_tutor);
        $this->assertNotNull($seguimiento->confirmado_at);
    }

    #[Test]
    public function profesor_no_tutor_no_puede_confirmar(): void
    {
        $tutor      = User::factory()->create(['rol' => 'profesor']);
        $otro       = User::factory()->create(['rol' => 'profesor']);
        $asignacion = AsignacionFct::factory()->create([
            'tutor_ies_id' => $tutor->id,
            'estado'       => 'activa',
        ]);
        $seguimiento = SeguimientoDiario::factory()->create([
            'asignacion_id'    => $asignacion->id,
            'confirmado_tutor' => false,
        ]);

        $response = $this->actingAs($otro)->post(
            route('asignaciones.seguimientos.confirmar', [$asignacion, $seguimiento]),
            []
        );

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_puede_confirmar_cualquier_seguimiento(): void
    {
        $admin      = User::factory()->create(['rol' => 'admin']);
        $asignacion = AsignacionFct::factory()->create(['estado' => 'activa']);
        $seguimiento = SeguimientoDiario::factory()->create([
            'asignacion_id'    => $asignacion->id,
            'confirmado_tutor' => false,
        ]);

        $response = $this->actingAs($admin)->post(
            route('asignaciones.seguimientos.confirmar', [$asignacion, $seguimiento]),
            []
        );

        $response->assertRedirect();
        $this->assertTrue($seguimiento->fresh()->confirmado_tutor);
    }

    // =========================================================================
    // Acceso via token tutor empresa
    // =========================================================================

    #[Test]
    public function tutor_empresa_accede_con_token_valido(): void
    {
        $asignacion = AsignacionFct::factory()->create(['estado' => 'activa']);
        $token      = TokenTutorEmpresa::factory()->for($asignacion, 'asignacion')->create();

        $response = $this->get(route('tutor.acceso', $token->token));

        $response->assertOk();
        $token->refresh();
        $this->assertNotNull($token->usado_at);
    }

    #[Test]
    public function tutor_empresa_recibe_404_con_token_expirado(): void
    {
        $asignacion = AsignacionFct::factory()->create();
        $token      = TokenTutorEmpresa::factory()->for($asignacion, 'asignacion')->expirado()->create();

        $response = $this->get(route('tutor.acceso', $token->token));

        $response->assertNotFound();
    }

    #[Test]
    public function tutor_empresa_puede_comentar_seguimiento(): void
    {
        $asignacion  = AsignacionFct::factory()->create(['estado' => 'activa']);
        $token       = TokenTutorEmpresa::factory()->for($asignacion, 'asignacion')->create();
        $seguimiento = SeguimientoDiario::factory()->create([
            'asignacion_id' => $asignacion->id,
        ]);

        $response = $this->post(
            route('tutor.comentar', ['token' => $token->token, 'seguimiento' => $seguimiento]),
            ['comentario_tutor' => 'Muy buen trabajo.']
        );

        $response->assertRedirect();
        $this->assertEquals('Muy buen trabajo.', $seguimiento->fresh()->comentario_tutor);
    }

    #[Test]
    public function tutor_empresa_no_puede_comentar_seguimiento_de_otra_asignacion(): void
    {
        $asignacion1 = AsignacionFct::factory()->create();
        $asignacion2 = AsignacionFct::factory()->create();
        $token       = TokenTutorEmpresa::factory()->for($asignacion1, 'asignacion')->create();
        $seguimiento = SeguimientoDiario::factory()->create([
            'asignacion_id' => $asignacion2->id,
        ]);

        $response = $this->post(
            route('tutor.comentar', ['token' => $token->token, 'seguimiento' => $seguimiento]),
            ['comentario_tutor' => 'Intento cruzado.']
        );

        $response->assertStatus(403);
    }
}
