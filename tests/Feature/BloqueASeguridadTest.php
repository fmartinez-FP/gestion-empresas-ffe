<?php

namespace Tests\Feature;

use App\Livewire\GestorDirecciones;
use App\Models\Alumno;
use App\Models\CicloFormativo;
use App\Models\Configuracion;
use App\Models\Direccion;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\PersonaContacto;
use App\Models\User;
use App\Services\ExportExcelService;
use App\Services\ImportExcelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regresión de los fallos críticos/de seguridad detectados en el análisis
 * (Bloque A): autorización de edición de alumnos, de las acciones Livewire
 * de sedes, de la importación/exportación de empresas, del duplicado y de
 * la validación de ciclos.
 */
class BloqueASeguridadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Configuracion::setCursoActivo('2025-2026');
    }

    private function usuario(string $rol): User
    {
        return User::factory()->create(['rol' => $rol, 'activo' => true]);
    }

    private function xlsx(array $filas): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($filas, null, 'A1');
        $ruta = tempnam(sys_get_temp_dir(), 'imp') . '.xlsx';
        (new Xlsx($spreadsheet))->save($ruta);

        return $ruta;
    }

    private function subida(string $ruta): UploadedFile
    {
        return new UploadedFile($ruta, 'empresas.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    // =========================================================================
    // Alumnos: editar respeta el mismo scope que ver
    // =========================================================================

    #[Test]
    public function profesor_no_tutor_no_puede_editar_ni_actualizar_alumno_ajeno(): void
    {
        $profesor = $this->usuario('profesor');
        $alumno = Alumno::factory()->create(['email' => 'original@example.com']);

        $this->actingAs($profesor)->get(route('alumnos.edit', $alumno))->assertForbidden();

        $this->actingAs($profesor)
            ->put(route('alumnos.update', $alumno), ['email' => 'hack@example.com', 'telefono' => '600000000'])
            ->assertForbidden();

        $this->assertSame('original@example.com', $alumno->fresh()->email);
    }

    #[Test]
    public function profesor_tutor_puede_editar_contacto_de_su_alumno(): void
    {
        $profesor = $this->usuario('profesor');
        $grupo = Grupo::factory()->create();
        $profesor->sincronizarGruposTutor([$grupo->id]);
        $alumno = Alumno::factory()->create(['grupo_id' => $grupo->id]);

        $this->actingAs($profesor)->get(route('alumnos.edit', $alumno))->assertOk();

        $this->actingAs($profesor)
            ->put(route('alumnos.update', $alumno), ['email' => 'nuevo@example.com', 'telefono' => '611111111'])
            ->assertRedirect(route('alumnos.show', $alumno));

        $this->assertSame('nuevo@example.com', $alumno->fresh()->email);
    }

    // =========================================================================
    // Livewire GestorDirecciones: cada acción autoriza
    // =========================================================================

    #[Test]
    public function profesor_sin_relacion_no_puede_eliminar_sede_por_livewire(): void
    {
        $responsable = $this->usuario('profesor');
        $empresa = Empresa::factory()->create(['creador_id' => $responsable->id]);
        $sede = Direccion::factory()->create(['empresa_id' => $empresa->id]);

        Livewire::actingAs($this->usuario('profesor'))
            ->test(GestorDirecciones::class, ['empresa' => $empresa])
            ->call('eliminar', $sede->id)
            ->assertForbidden();

        $this->assertDatabaseHas('direcciones', ['id' => $sede->id]);
    }

    #[Test]
    public function profesor_sin_relacion_no_puede_crear_ni_marcar_principal_por_livewire(): void
    {
        $responsable = $this->usuario('profesor');
        $empresa = Empresa::factory()->create(['creador_id' => $responsable->id]);
        $sede = Direccion::factory()->create(['empresa_id' => $empresa->id]);

        // Un componente por acción: tras un 403 Livewire descarta la petición.
        Livewire::actingAs($this->usuario('profesor'))
            ->test(GestorDirecciones::class, ['empresa' => $empresa])
            ->set('nombre_via', 'Calle Falsa')
            ->call('guardar')
            ->assertForbidden();

        Livewire::actingAs($this->usuario('profesor'))
            ->test(GestorDirecciones::class, ['empresa' => $empresa])
            ->call('marcarPrincipal', $sede->id)
            ->assertForbidden();

        $this->assertDatabaseMissing('direcciones', ['nombre_via' => 'Calle Falsa']);
    }

    #[Test]
    public function creador_de_la_empresa_si_puede_eliminar_su_sede_por_livewire(): void
    {
        $responsable = $this->usuario('profesor');
        $empresa = Empresa::factory()->create(['creador_id' => $responsable->id]);
        $sede = Direccion::factory()->create(['empresa_id' => $empresa->id]);

        Livewire::actingAs($responsable)
            ->test(GestorDirecciones::class, ['empresa' => $empresa])
            ->call('eliminar', $sede->id)
            ->assertSuccessful();

        $this->assertDatabaseMissing('direcciones', ['id' => $sede->id]);
    }

    // =========================================================================
    // Importación de empresas
    // =========================================================================

    #[Test]
    public function solo_admin_y_responsable_ffe_acceden_a_la_importacion_de_empresas(): void
    {
        foreach (['profesor', 'responsable_ciclo', 'alumno'] as $rol) {
            $u = $this->usuario($rol);
            $this->actingAs($u)->get(route('empresas.import'))->assertForbidden();
            $this->actingAs($u)->get(route('empresas.import.plantilla'))->assertForbidden();
            $this->actingAs($u)
                ->post(route('empresas.import.store'), ['archivo' => $this->subida($this->xlsx([['Nombre', 'CIF'], ['X', 'A1']]))])
                ->assertForbidden();
        }

        foreach (['admin', 'responsable_ffe'] as $rol) {
            $this->actingAs($this->usuario($rol))->get(route('empresas.import'))->assertOk();
        }
    }

    #[Test]
    public function la_plantilla_oficial_se_importa_correctamente(): void
    {
        $admin = $this->usuario('admin');

        $plantilla = (new ImportExcelService())->generarPlantilla();
        $respuesta = $this->actingAs($admin)
            ->post(route('empresas.import.store'), ['archivo' => $this->subida($plantilla)]);

        $respuesta->assertRedirect(route('empresas.index'));
        $respuesta->assertSessionHas('success');
        $this->assertDatabaseHas('empresas', ['cif' => 'B12345678', 'nombre' => 'Empresa Ejemplo S.L.']);
        $this->assertDatabaseHas('empresas', ['cif' => 'A87654321']);
    }

    #[Test]
    public function importar_sin_columnas_de_convenio_o_notas_no_las_vacia_al_actualizar(): void
    {
        $admin = $this->usuario('admin');
        $empresa = Empresa::factory()->create([
            'cif' => 'B11111111',
            'nombre' => 'Vieja',
            'num_convenio' => 'CONV-1',
            'notas' => 'No borrar',
        ]);

        $ruta = $this->xlsx([['Nombre', 'CIF'], ['Nueva', 'B11111111']]);
        $resultado = (new ImportExcelService())->importarEmpresas($ruta, $admin->id, true);

        $this->assertTrue($resultado['success']);
        $this->assertSame(1, $resultado['actualizadas']);
        $empresa->refresh();
        $this->assertSame('Nueva', $empresa->nombre);
        $this->assertSame('CONV-1', $empresa->num_convenio);
        $this->assertSame('No borrar', $empresa->notas);
    }

    // =========================================================================
    // Exportación de empresas
    // =========================================================================

    private function celdas(string $ruta): array
    {
        return IOFactory::load($ruta)->getActiveSheet()->toArray(null, true, false, false);
    }

    #[Test]
    public function la_exportacion_solo_incluye_contactos_de_empresas_que_el_usuario_gestiona(): void
    {
        $profesor = $this->usuario('profesor');
        $propia = Empresa::factory()->create(['nombre' => 'Propia', 'creador_id' => $profesor->id]);
        $ajena = Empresa::factory()->create(['nombre' => 'Ajena']);
        foreach ([$propia, $ajena] as $e) {
            PersonaContacto::factory()->create(['empresa_id' => $e->id, 'principal' => true, 'nombre' => 'Contacto ' . $e->nombre, 'telefono' => '600' . $e->id, 'email' => $e->nombre . '@x.es']);
        }

        $filas = collect($this->celdas((new ExportExcelService())->exportarEmpresas([], $profesor)))->skip(1)->keyBy(0);

        $this->assertSame('Contacto Propia', $filas['Propia'][4]);
        $this->assertSame('', (string) $filas['Ajena'][2], 'teléfono de empresa ajena');
        $this->assertSame('', (string) $filas['Ajena'][3], 'email de empresa ajena');
        $this->assertSame('', (string) $filas['Ajena'][4], 'contacto de empresa ajena');
    }

    #[Test]
    public function la_exportacion_no_interpreta_texto_de_usuario_como_formula(): void
    {
        $admin = $this->usuario('admin');
        Empresa::factory()->create(['nombre' => '=HYPERLINK("http://evil","x")']);

        $ruta = (new ExportExcelService())->exportarEmpresas([], $admin);
        $hoja = IOFactory::load($ruta)->getActiveSheet();

        $this->assertSame('s', $hoja->getCell('A2')->getDataType());
        $this->assertSame('=HYPERLINK("http://evil","x")', $hoja->getCell('A2')->getValue());
    }

    #[Test]
    public function la_busqueda_no_revela_empresas_ajenas_por_su_contacto(): void
    {
        $profesor = $this->usuario('profesor');
        $ajena = Empresa::factory()->create(['nombre' => 'Ajena SL']);
        PersonaContacto::factory()->create(['empresa_id' => $ajena->id, 'nombre' => 'Zacarias Secreto', 'email' => 'zac@secreto.es']);
        $propia = Empresa::factory()->create(['nombre' => 'Propia SL', 'creador_id' => $profesor->id]);
        PersonaContacto::factory()->create(['empresa_id' => $propia->id, 'nombre' => 'Zacarias Propio']);

        $res = $this->actingAs($profesor)->getJson('/buscar?q=Zacarias')->assertOk()->json();

        $this->assertSame(['Propia SL'], array_column($res, 'nombre'));
        $this->assertSame([], $this->actingAs($profesor)->getJson('/buscar?q=zac@secreto')->json());

        $admin = $this->usuario('admin');
        $this->assertCount(2, $this->actingAs($admin)->getJson('/buscar?q=Zacarias')->json());
    }

    // =========================================================================
    // Duplicar empresa (precarga del formulario)
    // =========================================================================

    #[Test]
    public function duplicar_precarga_el_formulario_sin_crear_ninguna_empresa(): void
    {
        $this->seed(\Database\Seeders\CicloFormativoSeeder::class);
        $user = $this->usuario('profesor');
        $origen = Empresa::factory()->create(['nombre' => 'Origen SL', 'notas' => 'Notas origen']);
        $ciclo = CicloFormativo::first();
        $origen->sincronizarCiclos([$ciclo->id => ['acepta_primero' => true, 'acepta_segundo' => false]]);

        $antes = Empresa::count();
        $res = $this->actingAs($user)->get(route('empresas.create', ['duplicar_de' => $origen->id]));

        $res->assertOk()->assertSee('Origen SL (copia)')->assertSee('Notas origen');
        $this->assertSame($antes, Empresa::count());
        // El antiguo POST /empresas/{id}/duplicar ya no existe.
        $this->actingAs($user)->post("/empresas/{$origen->id}/duplicar")->assertNotFound();
    }

    #[Test]
    public function guardar_un_duplicado_copia_ciclos_sedes_y_contactos_si_hay_permiso(): void
    {
        $this->seed(\Database\Seeders\CicloFormativoSeeder::class);
        $admin = $this->usuario('admin');
        $origen = Empresa::factory()->create();
        $ciclo = CicloFormativo::first();
        Direccion::factory()->create(['empresa_id' => $origen->id, 'nombre_via' => 'Mayor', 'principal' => true]);
        PersonaContacto::factory()->create(['empresa_id' => $origen->id, 'nombre' => 'Ana', 'principal' => true]);

        foreach (['NUEVA0001', 'NUEVA0002'] as $cif) {
            $this->actingAs($admin)->post(route('empresas.store'), [
                'nombre' => 'Copia ' . $cif,
                'cif' => $cif,
                'duplicar_de' => $origen->id,
                'ciclos' => [$ciclo->id => ['seleccionado' => 1, 'acepta_primero' => 1]],
            ])->assertSessionHasNoErrors();

            $copia = Empresa::where('cif', $cif)->firstOrFail();
            $this->assertCount(1, $copia->ciclos);
            $this->assertSame(['Mayor'], $copia->direcciones->pluck('nombre_via')->all());
            $this->assertSame(['Ana'], $copia->personasContacto->pluck('nombre')->all());
        }
    }

    #[Test]
    public function guardar_un_duplicado_no_copia_contactos_ni_sedes_sin_permiso_sobre_el_origen(): void
    {
        $profesor = $this->usuario('profesor');
        $origen = Empresa::factory()->create();
        Direccion::factory()->create(['empresa_id' => $origen->id]);
        PersonaContacto::factory()->create(['empresa_id' => $origen->id, 'nombre' => 'Privado']);

        $this->actingAs($profesor)->post(route('empresas.store'), [
            'nombre' => 'Copia', 'cif' => 'COPIA0001', 'duplicar_de' => $origen->id,
        ])->assertSessionHasNoErrors();

        $copia = Empresa::where('cif', 'COPIA0001')->firstOrFail();
        $this->assertCount(0, $copia->personasContacto);
        $this->assertCount(0, $copia->direcciones);
    }

    // =========================================================================
    // Crear empresa con ciclo inexistente
    // =========================================================================

    #[Test]
    public function crear_empresa_con_ciclo_inexistente_da_error_de_validacion_y_no_crea_nada(): void
    {
        $user = $this->usuario('profesor');

        $this->actingAs($user)->post(route('empresas.store'), [
            'nombre' => 'Rota SL',
            'cif' => 'ROTA00001',
            'ciclos' => [99999 => ['seleccionado' => 1, 'acepta_primero' => 1]],
        ])->assertSessionHasErrors('ciclos');

        $this->assertDatabaseMissing('empresas', ['cif' => 'ROTA00001']);
    }

    #[Test]
    public function editar_empresa_con_ciclo_inexistente_da_error_de_validacion(): void
    {
        $admin = $this->usuario('admin');
        $empresa = Empresa::factory()->create();

        $this->actingAs($admin)->put(route('empresas.update', $empresa), [
            'nombre' => $empresa->nombre,
            'cif' => $empresa->cif,
            'ciclos_enviados' => 1,
            'ciclos' => [99999 => ['seleccionado' => 1]],
        ])->assertSessionHasErrors('ciclos');
    }
}
