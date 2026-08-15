<?php

namespace Tests\Feature;

use App\Models\AsignacionFct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlanFormativoControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function tutor_ies_puede_ver_formulario_de_plan_formativo(): void
    {
        $tutor      = User::factory()->create(['rol' => 'profesor']);
        $asignacion = AsignacionFct::factory()->create(['tutor_ies_id' => $tutor->id]);

        $response = $this->actingAs($tutor)
            ->get(route('documentos.plan-formativo.form', $asignacion));

        $response->assertOk();
    }

    #[Test]
    public function profesor_ajeno_no_puede_ver_formulario_de_plan_formativo(): void
    {
        $tutor      = User::factory()->create(['rol' => 'profesor']);
        $otro       = User::factory()->create(['rol' => 'profesor']);
        $asignacion = AsignacionFct::factory()->create(['tutor_ies_id' => $tutor->id]);

        $response = $this->actingAs($otro)
            ->get(route('documentos.plan-formativo.form', $asignacion));

        $response->assertForbidden();
    }

    #[Test]
    public function admin_puede_ver_formulario_de_cualquier_plan_formativo(): void
    {
        $admin      = User::factory()->create(['rol' => 'admin']);
        $asignacion = AsignacionFct::factory()->create();

        $response = $this->actingAs($admin)
            ->get(route('documentos.plan-formativo.form', $asignacion));

        $response->assertOk();
    }
}
