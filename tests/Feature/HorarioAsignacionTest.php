<?php

namespace Tests\Feature;

use App\Models\AjusteHorasSemana;
use App\Models\AsignacionFct;
use App\Models\CalendarioAsignacion;
use App\Models\HorarioAsignacion;
use App\Models\SeguimientoDiario;
use App\Models\User;
use App\Services\HorarioAsignacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HorarioAsignacionTest extends TestCase
{
    use RefreshDatabase;

    private function servicio(): HorarioAsignacionService
    {
        return new HorarioAsignacionService();
    }

    private function asignacion(array $attrs = []): AsignacionFct
    {
        return AsignacionFct::factory()->create(array_merge([
            'fecha_inicio' => '2026-09-07', // lunes
            'fecha_fin'    => '2026-09-18', // viernes de la semana siguiente
        ], $attrs));
    }

    /** @test */
    public function horas_diarias_calcula_jornada_continua()
    {
        $horario = HorarioAsignacion::factory()->make([
            'entrada_manana' => '08:00',
            'salida_manana'  => '15:00',
        ]);

        $this->assertEquals(7.0, $this->servicio()->horasDiarias($horario));
    }

    /** @test */
    public function horas_diarias_calcula_jornada_partida()
    {
        $horario = HorarioAsignacion::factory()->partida()->make([
            'entrada_manana' => '08:00',
            'salida_manana'  => '14:00',
            'entrada_tarde'  => '16:00',
            'salida_tarde'   => '19:00',
        ]);

        $this->assertEquals(9.0, $this->servicio()->horasDiarias($horario));
    }

    /** @test */
    public function horas_previstas_ignora_dias_sin_horario_configurado()
    {
        $asignacion = $this->asignacion([
            'fecha_inicio' => '2026-09-07', // lunes
            'fecha_fin'    => '2026-09-11', // viernes
        ]);

        foreach (['lunes', 'martes', 'miercoles'] as $dia) {
            HorarioAsignacion::factory()->create([
                'asignacion_id'  => $asignacion->id,
                'dia'            => $dia,
                'entrada_manana' => '08:00',
                'salida_manana'  => '15:00',
            ]);
        }

        $asignacion->refresh();

        $this->assertEquals(21.0, $this->servicio()->horasPrevistas($asignacion));
    }

    /** @test */
    public function horas_previstas_excluye_festivos_no_lectivos_y_bajas()
    {
        $asignacion = $this->asignacion([
            'fecha_inicio' => '2026-09-07',
            'fecha_fin'    => '2026-09-11',
        ]);

        foreach (['lunes', 'martes', 'miercoles', 'jueves', 'viernes'] as $dia) {
            HorarioAsignacion::factory()->create([
                'asignacion_id'  => $asignacion->id,
                'dia'            => $dia,
                'entrada_manana' => '08:00',
                'salida_manana'  => '15:00',
            ]);
        }

        CalendarioAsignacion::factory()->festivo()->create([
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-09-08', // martes
        ]);
        CalendarioAsignacion::factory()->noLectivo()->create([
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-09-09', // miércoles
        ]);
        CalendarioAsignacion::factory()->baja()->create([
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-09-10', // jueves
        ]);

        $asignacion->refresh();

        // 5 días x 7h = 35h, menos 3 días excluidos = 2 días x 7h = 14h
        $this->assertEquals(14.0, $this->servicio()->horasPrevistas($asignacion));
    }

    /** @test */
    public function horas_previstas_lanza_excepcion_si_falta_fecha_inicio()
    {
        $asignacion = $this->asignacion(['fecha_inicio' => null]);

        $this->expectException(\InvalidArgumentException::class);
        $this->servicio()->horasPrevistas($asignacion);
    }

    /** @test */
    public function horas_previstas_lanza_excepcion_si_falta_fecha_fin()
    {
        $asignacion = $this->asignacion(['fecha_fin' => null]);

        $this->expectException(\InvalidArgumentException::class);
        $this->servicio()->horasPrevistas($asignacion);
    }

    /** @test */
    public function horas_realizadas_solo_cuenta_seguimientos_confirmados()
    {
        $asignacion = $this->asignacion();

        HorarioAsignacion::factory()->create([
            'asignacion_id'  => $asignacion->id,
            'dia'            => 'lunes',
            'entrada_manana' => '08:00',
            'salida_manana'  => '15:00',
        ]);

        SeguimientoDiario::factory()->confirmado()->create([
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-09-07', // lunes
        ]);

        SeguimientoDiario::factory()->create([
            'asignacion_id'    => $asignacion->id,
            'fecha'            => '2026-09-14',
            'confirmado_tutor' => false,
        ]);

        $asignacion->refresh();

        $this->assertEquals(7.0, $this->servicio()->horasRealizadas($asignacion));
    }

    /** @test */
    public function horas_realizadas_suma_siempre_los_ajustes_semanales()
    {
        $asignacion = $this->asignacion();

        HorarioAsignacion::factory()->create([
            'asignacion_id'  => $asignacion->id,
            'dia'            => 'lunes',
            'entrada_manana' => '08:00',
            'salida_manana'  => '15:00',
        ]);

        SeguimientoDiario::factory()->confirmado()->create([
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-09-07',
        ]);

        AjusteHorasSemana::factory()->create([
            'asignacion_id' => $asignacion->id,
            'semana'        => '2026-09-07',
            'ajuste'        => -2.5,
            'created_by_id' => User::factory(),
        ]);

        $asignacion->refresh();

        // 7h confirmadas - 2.5h de ajuste = 4.5h, sin importar si la semana está "confirmada"
        $this->assertEquals(4.5, $this->servicio()->horasRealizadas($asignacion));
    }

    /** @test */
    public function generar_texto_horario_agrupa_dias_consecutivos_con_mismo_horario()
    {
        $asignacion = $this->asignacion();

        foreach (['lunes', 'martes', 'miercoles', 'jueves'] as $dia) {
            HorarioAsignacion::factory()->create([
                'asignacion_id'  => $asignacion->id,
                'dia'            => $dia,
                'entrada_manana' => '08:00',
                'salida_manana'  => '15:00',
            ]);
        }

        HorarioAsignacion::factory()->create([
            'asignacion_id'  => $asignacion->id,
            'dia'            => 'viernes',
            'entrada_manana' => '08:00',
            'salida_manana'  => '14:00',
        ]);

        $asignacion->refresh();

        $this->assertEquals(
            'Lunes a Jueves 08:00-15:00, Viernes 08:00-14:00',
            $this->servicio()->generarTextoHorario($asignacion)
        );
    }

    /** @test */
    public function generar_texto_horario_incluye_tramo_de_tarde_en_jornada_partida()
    {
        $asignacion = $this->asignacion();

        HorarioAsignacion::factory()->create([
            'asignacion_id'  => $asignacion->id,
            'dia'            => 'lunes',
            'entrada_manana' => '08:00',
            'salida_manana'  => '14:00',
            'entrada_tarde'  => '16:00',
            'salida_tarde'   => '19:00',
        ]);

        $asignacion->refresh();

        $this->assertEquals(
            'Lunes 08:00-14:00 y 16:00-19:00',
            $this->servicio()->generarTextoHorario($asignacion)
        );
    }

    /** @test */
    public function advertencias_detecta_exceso_diario()
    {
        $asignacion = $this->asignacion();

        HorarioAsignacion::factory()->jornadaLarga()->create([
            'asignacion_id' => $asignacion->id,
            'dia'           => 'lunes',
        ]);

        $asignacion->refresh();

        $advertencias = $this->servicio()->advertencias($asignacion);

        $this->assertCount(1, $advertencias);
        $this->assertEquals('exceso_diario', $advertencias[0]['tipo']);
    }

    /** @test */
    public function advertencias_detecta_exceso_semanal()
    {
        $asignacion = $this->asignacion();

        foreach (['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'] as $dia) {
            HorarioAsignacion::factory()->create([
                'asignacion_id'  => $asignacion->id,
                'dia'            => $dia,
                'entrada_manana' => '08:00',
                'salida_manana'  => '15:00', // 7h x 6 días = 42h
            ]);
        }

        $asignacion->refresh();

        $tipos = array_column($this->servicio()->advertencias($asignacion), 'tipo');

        $this->assertContains('exceso_semanal', $tipos);
    }

    /** @test */
    public function validar_horarios_detecta_dia_duplicado()
    {
        $errores = $this->servicio()->validarHorarios([
            ['dia' => 'lunes', 'entrada_manana' => '08:00', 'salida_manana' => '15:00'],
            ['dia' => 'lunes', 'entrada_manana' => '09:00', 'salida_manana' => '16:00'],
        ]);

        $this->assertNotEmpty($errores);
    }

    /** @test */
    public function validar_horarios_detecta_solape_entre_manana_y_tarde()
    {
        $errores = $this->servicio()->validarHorarios([
            [
                'dia'            => 'lunes',
                'entrada_manana' => '08:00',
                'salida_manana'  => '15:00',
                'entrada_tarde'  => '14:00',
                'salida_tarde'   => '18:00',
            ],
        ]);

        $this->assertNotEmpty($errores);
    }

    /** @test */
    public function guardar_persiste_horarios_y_recalcula_num_horas_y_horario()
    {
        $asignacion = $this->asignacion([
            'fecha_inicio' => '2026-09-07',
            'fecha_fin'    => '2026-09-11',
        ]);

        $this->servicio()->guardar($asignacion, [
            ['dia' => 'lunes', 'entrada_manana' => '08:00', 'salida_manana' => '15:00'],
            ['dia' => 'martes', 'entrada_manana' => '08:00', 'salida_manana' => '15:00'],
        ]);

        $asignacion->refresh();

        $this->assertDatabaseCount('horario_asignacion', 2);
        $this->assertEquals(14.0, $asignacion->num_horas);
        $this->assertEquals('Lunes a Martes 08:00-15:00', $asignacion->horario);
    }

    /** @test */
    public function guardar_reemplaza_horarios_previos_en_vez_de_acumularlos()
    {
        $asignacion = $this->asignacion([
            'fecha_inicio' => '2026-09-07',
            'fecha_fin'    => '2026-09-11',
        ]);

        $this->servicio()->guardar($asignacion, [
            ['dia' => 'lunes', 'entrada_manana' => '08:00', 'salida_manana' => '15:00'],
        ]);

        $this->servicio()->guardar($asignacion->fresh(), [
            ['dia' => 'martes', 'entrada_manana' => '08:00', 'salida_manana' => '15:00'],
        ]);

        $this->assertDatabaseCount('horario_asignacion', 1);
        $this->assertDatabaseHas('horario_asignacion', [
            'asignacion_id' => $asignacion->id,
            'dia'           => 'martes',
        ]);
    }

    /** @test */
    public function guardar_lanza_validation_exception_si_los_horarios_no_son_validos()
    {
        $asignacion = $this->asignacion();

        $this->expectException(ValidationException::class);

        $this->servicio()->guardar($asignacion, [
            ['dia' => 'lunes', 'entrada_manana' => '15:00', 'salida_manana' => '08:00'],
        ]);
    }

    /** @test */
    public function no_se_puede_duplicar_dia_para_la_misma_asignacion()
    {
        $asignacion = $this->asignacion();

        HorarioAsignacion::factory()->create(['asignacion_id' => $asignacion->id, 'dia' => 'lunes']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        HorarioAsignacion::factory()->create(['asignacion_id' => $asignacion->id, 'dia' => 'lunes']);
    }

    /** @test */
    public function relacion_horarios_ordena_lunes_a_domingo()
    {
        $asignacion = $this->asignacion();

        foreach (['viernes', 'lunes', 'miercoles'] as $dia) {
            HorarioAsignacion::factory()->create(['asignacion_id' => $asignacion->id, 'dia' => $dia]);
        }

        $dias = $asignacion->horarios()->pluck('dia')->all();

        $this->assertEquals(['lunes', 'miercoles', 'viernes'], $dias);
    }

    /** @test */
    public function horas_semana_calcula_previstas_y_confirmadas_de_lunes_a_viernes()
    {
        $asignacion = $this->asignacion();

        foreach (['lunes', 'martes', 'miercoles', 'jueves', 'viernes'] as $dia) {
            HorarioAsignacion::factory()->create([
                'asignacion_id'  => $asignacion->id,
                'dia'            => $dia,
                'entrada_manana' => '08:00',
                'salida_manana'  => '15:00',
                'entrada_tarde'  => null,
                'salida_tarde'   => null,
            ]);
        }

        SeguimientoDiario::factory()->create([
            'asignacion_id'    => $asignacion->id,
            'fecha'            => '2026-09-07', // lunes
            'confirmado_tutor' => true,
        ]);
        SeguimientoDiario::factory()->create([
            'asignacion_id'    => $asignacion->id,
            'fecha'            => '2026-09-08', // martes
            'confirmado_tutor' => true,
        ]);
        SeguimientoDiario::factory()->create([
            'asignacion_id'    => $asignacion->id,
            'fecha'            => '2026-09-09', // miercoles, sin confirmar
            'confirmado_tutor' => false,
        ]);

        $asignacion->unsetRelation('horarios');
        $resultado = $this->servicio()->horasSemana($asignacion, \Carbon\Carbon::parse('2026-09-07'));

        $this->assertEquals(35.0, $resultado['previstas']);
        $this->assertEquals(14.0, $resultado['confirmadas']);
        $this->assertEquals(0.0, $resultado['ajuste']);
        $this->assertEquals(14.0, $resultado['realizadas']);
    }

    /** @test */
    public function horas_semana_excluye_dia_marcado_como_festivo(): void
    {
        $asignacion = $this->asignacion();

        foreach (['lunes', 'martes', 'miercoles', 'jueves', 'viernes'] as $dia) {
            HorarioAsignacion::factory()->create([
                'asignacion_id'  => $asignacion->id,
                'dia'            => $dia,
                'entrada_manana' => '08:00',
                'salida_manana'  => '15:00',
                'entrada_tarde'  => null,
                'salida_tarde'   => null,
            ]);
        }

        CalendarioAsignacion::create([
            'asignacion_id' => $asignacion->id,
            'fecha'         => '2026-09-09', // miercoles
            'tipo'          => 'festivo',
            'motivo'        => 'Prueba',
        ]);

        $asignacion->unsetRelation('horarios');
        $resultado = $this->servicio()->horasSemana($asignacion, \Carbon\Carbon::parse('2026-09-07'));

        $this->assertEquals(28.0, $resultado['previstas']); // 4 dias, no 5
    }

    /** @test */
    public function horas_semana_incluye_el_ajuste_de_esa_semana_concreta(): void
    {
        $asignacion = $this->asignacion();
        $usuario    = User::factory()->create();

        HorarioAsignacion::factory()->create([
            'asignacion_id'  => $asignacion->id,
            'dia'            => 'lunes',
            'entrada_manana' => '08:00',
            'salida_manana'  => '15:00',
            'entrada_tarde'  => null,
            'salida_tarde'   => null,
        ]);

        AjusteHorasSemana::create([
            'asignacion_id' => $asignacion->id,
            'semana'        => '2026-09-07',
            'ajuste'        => 2.5,
            'motivo'        => 'Prueba',
            'created_by_id' => $usuario->id,
        ]);

        $asignacion->unsetRelation('horarios');
        $resultado = $this->servicio()->horasSemana($asignacion, \Carbon\Carbon::parse('2026-09-07'));

        $this->assertEquals(2.5, $resultado['ajuste']);
        $this->assertEquals(2.5, $resultado['realizadas']); // 0 confirmadas + 2.5 ajuste
    }
}
