<?php

namespace Tests\Feature;

use App\Models\CicloFormativo;
use App\Models\CriterioEvaluacion;
use App\Models\ElegibleFfe;
use App\Models\ElegibleFfeCe;
use App\Models\ModuloProfesional;
use App\Models\ResultadoAprendizaje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ElegibleCeTest extends TestCase
{
    use RefreshDatabase;

    private function responsable(): User
    {
        return User::factory()->create(['rol' => 'responsable_ffe']);
    }

    private function profesor(): User
    {
        return User::factory()->create(['rol' => 'profesor']);
    }

    private function ceConRa(): array
    {
        $ciclo  = CicloFormativo::factory()->create();
        $modulo = ModuloProfesional::factory()->create(['ciclo_id' => $ciclo->id]);
        $ra     = ResultadoAprendizaje::factory()->create(['modulo_id' => $modulo->id]);
        $ce     = CriterioEvaluacion::factory()->create(['resultado_aprendizaje_id' => $ra->id]);
        return compact('ra', 'ce');
    }

    #[Test]
    public function responsable_puede_marcar_ce_como_elegible(): void
    {
        ['ce' => $ce] = $this->ceConRa();
        $user = $this->responsable();

        $this->actingAs($user)
            ->postJson(route('admin.curriculum.elegibles.toggle-ce'), [
                'criterio_evaluacion_id' => $ce->id,
                'curso_academico'        => '2025-2026',
            ])
            ->assertOk()
            ->assertJson(['elegible' => true]);

        $this->assertDatabaseHas('elegibles_ffe_ce', [
            'criterio_evaluacion_id' => $ce->id,
            'curso_academico'        => '2025-2026',
        ]);
    }

    #[Test]
    public function toggle_ce_elimina_si_ya_existia(): void
    {
        ['ce' => $ce] = $this->ceConRa();
        $user = $this->responsable();

        ElegibleFfeCe::create([
            'criterio_evaluacion_id' => $ce->id,
            'curso_academico'        => '2025-2026',
            'created_by_id'          => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson(route('admin.curriculum.elegibles.toggle-ce'), [
                'criterio_evaluacion_id' => $ce->id,
                'curso_academico'        => '2025-2026',
            ])
            ->assertOk()
            ->assertJson(['elegible' => false]);

        $this->assertDatabaseMissing('elegibles_ffe_ce', [
            'criterio_evaluacion_id' => $ce->id,
            'curso_academico'        => '2025-2026',
        ]);
    }

    #[Test]
    public function profesor_no_puede_marcar_ce_elegible(): void
    {
        ['ce' => $ce] = $this->ceConRa();

        $this->actingAs($this->profesor())
            ->postJson(route('admin.curriculum.elegibles.toggle-ce'), [
                'criterio_evaluacion_id' => $ce->id,
                'curso_academico'        => '2025-2026',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function desmarcar_ra_elimina_sus_ce_elegibles(): void
    {
        ['ra' => $ra, 'ce' => $ce] = $this->ceConRa();
        $user = $this->responsable();

        ElegibleFfe::create([
            'resultado_aprendizaje_id' => $ra->id,
            'curso_academico'          => '2025-2026',
            'created_by_id'            => $user->id,
        ]);
        ElegibleFfeCe::create([
            'criterio_evaluacion_id' => $ce->id,
            'curso_academico'        => '2025-2026',
            'created_by_id'          => $user->id,
        ]);

        // Desmarcar el RA
        $this->actingAs($user)
            ->postJson(route('admin.curriculum.elegibles.toggle'), [
                'resultado_aprendizaje_id' => $ra->id,
                'curso_academico'          => '2025-2026',
            ])
            ->assertOk()
            ->assertJson(['elegible' => false]);

        // El CE elegible debe haberse eliminado en cascada lógica
        $this->assertDatabaseMissing('elegibles_ffe_ce', [
            'criterio_evaluacion_id' => $ce->id,
            'curso_academico'        => '2025-2026',
        ]);
    }
}
