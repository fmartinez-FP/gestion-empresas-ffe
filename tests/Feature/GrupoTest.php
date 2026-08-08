<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\CicloFormativo;
use App\Models\Grupo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GrupoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function el_constraint_unico_ciclo_numero_curso_etiqueta_impide_duplicados(): void
    {
        $ciclo = CicloFormativo::factory()->create();

        Grupo::factory()->create([
            'ciclo_id'     => $ciclo->id,
            'numero_curso' => 1,
            'etiqueta'     => 'A',
        ]);

        $this->expectException(QueryException::class);

        Grupo::factory()->create([
            'ciclo_id'     => $ciclo->id,
            'numero_curso' => 1,
            'etiqueta'     => 'A',
        ]);
    }

    #[Test]
    public function el_mismo_ciclo_y_curso_permite_etiquetas_distintas(): void
    {
        $ciclo = CicloFormativo::factory()->create();

        $grupoA = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 1, 'etiqueta' => 'A']);
        $grupoB = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 1, 'etiqueta' => 'B']);

        $this->assertNotEquals($grupoA->id, $grupoB->id);
        $this->assertDatabaseCount('grupos', 2);
    }

    #[Test]
    public function etiqueta_completa_incluye_la_letra_cuando_existe(): void
    {
        $grupo = Grupo::factory()->create(['numero_curso' => 1, 'etiqueta' => 'A']);

        $this->assertSame('1º A', $grupo->etiqueta_completa);
    }

    #[Test]
    public function etiqueta_completa_omite_la_letra_cuando_esta_vacia(): void
    {
        $grupo = Grupo::factory()->create(['numero_curso' => 2, 'etiqueta' => '']);

        $this->assertSame('2º', $grupo->etiqueta_completa);
    }

    #[Test]
    public function etiqueta_con_ciclo_combina_codigo_del_ciclo_y_etiqueta_completa(): void
    {
        $ciclo = CicloFormativo::factory()->create(['codigo' => 'DAM']);
        $grupo = Grupo::factory()->create(['ciclo_id' => $ciclo->id, 'numero_curso' => 1, 'etiqueta' => 'A']);

        $this->assertSame('DAM — 1º A', $grupo->etiqueta_con_ciclo);
    }

    #[Test]
    public function relacion_ciclo_devuelve_el_ciclo_formativo_padre(): void
    {
        $ciclo = CicloFormativo::factory()->create();
        $grupo = Grupo::factory()->create(['ciclo_id' => $ciclo->id]);

        $this->assertTrue($grupo->ciclo->is($ciclo));
    }

    #[Test]
    public function relacion_alumnos_devuelve_solo_los_alumnos_de_ese_grupo(): void
    {
        $grupo      = Grupo::factory()->create();
        $otroGrupo  = Grupo::factory()->create();

        $alumnoEnGrupo = Alumno::factory()->create(['grupo_id' => $grupo->id]);
        Alumno::factory()->create(['grupo_id' => $otroGrupo->id]);

        $this->assertCount(1, $grupo->alumnos);
        $this->assertTrue($grupo->alumnos->first()->is($alumnoEnGrupo));
    }

    #[Test]
    public function scope_activos_excluye_los_grupos_inactivos(): void
    {
        Grupo::factory()->create(['activo' => true]);
        Grupo::factory()->inactivo()->create();

        $this->assertSame(1, Grupo::activos()->count());
    }
}
