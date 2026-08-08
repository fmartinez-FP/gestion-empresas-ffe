<?php

namespace Tests\Feature;

use App\Models\Configuracion;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GrupoTutorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function sync_preserva_historico_de_cursos_anteriores(): void
    {
        Configuracion::setCursoActivo('2025-2026');

        $profesor = User::factory()->create(['rol' => 'profesor']);
        $grupoA = Grupo::factory()->create();
        $grupoB = Grupo::factory()->create();

        \DB::table('profesor_tutor')->insert([
            'user_id' => $profesor->id,
            'grupo_id' => $grupoA->id,
            'curso_academico' => '2024-2025',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $profesor->sincronizarGruposTutor([$grupoB->id]);
        $profesor->sincronizarGruposTutor([$grupoA->id]);

        $filas = \DB::table('profesor_tutor')->where('user_id', $profesor->id)->get();

        $this->assertTrue($filas->contains(fn ($f) => $f->curso_academico === '2024-2025' && $f->grupo_id === $grupoA->id));

        $filasActivas = $filas->where('curso_academico', '2025-2026');
        $this->assertCount(1, $filasActivas);
        $this->assertEquals($grupoA->id, $filasActivas->first()->grupo_id);

        $this->assertCount(2, $filas);
    }

    #[Test]
    public function grupos_tutor_solo_devuelve_curso_activo(): void
    {
        Configuracion::setCursoActivo('2025-2026');

        $profesor = User::factory()->create(['rol' => 'profesor']);
        $grupo = Grupo::factory()->create();

        \DB::table('profesor_tutor')->insert([
            'user_id' => $profesor->id,
            'grupo_id' => $grupo->id,
            'curso_academico' => '2024-2025',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertCount(0, $profesor->gruposTutor);
    }
}
