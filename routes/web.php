<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\ColocacionController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\PreferenciasController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\CicloFormativoController;
use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\Admin\GrupoController;
use App\Http\Controllers\MapaController;
use App\Http\Controllers\NotificacionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Públicas (sin autenticación)
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (requieren autenticación)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Búsqueda global (API JSON)
    Route::get('/seguimientos', [SeguimientoController::class, 'index'])->name('seguimientos.index');
    Route::get('/buscar', [BusquedaController::class, 'buscar'])->name('buscar')->middleware('throttle:busqueda');
    
    // Preferencias de usuario
    Route::post('/preferencias/modo-oscuro', [PreferenciasController::class, 'toggleModoOscuro'])->name('preferencias.modo-oscuro');
    
    // Empresas
    Route::resource('empresas', EmpresaController::class);

    // Mapa de empresas
    Route::get('/empresas-mapa/datos', [MapaController::class, 'datos'])->name('mapa.datos');

    // Notificaciones
    Route::get('/notificaciones/{notificacion}/leer', [NotificacionController::class, 'leer'])->name('notificaciones.leer');
    Route::post('/empresas/{empresa}/renovar', [EmpresaController::class, 'renovar'])->name('empresas.renovar');
    Route::post('/empresas/{empresa}/duplicar', [EmpresaController::class, 'duplicar'])->name('empresas.duplicar');
    
    // Importación de empresas
    Route::get('/empresas-import', [ImportController::class, 'index'])->name('empresas.import');
    Route::post('/empresas-import', [ImportController::class, 'store'])->name('empresas.import.store');
    Route::get('/empresas-import/plantilla', [ImportController::class, 'plantilla'])->name('empresas.import.plantilla');
    
    // Colocaciones
    Route::get('/colocaciones', [ColocacionController::class, 'index'])->name('colocaciones.index');
    Route::get('/empresas/{empresa}/colocaciones/create', [ColocacionController::class, 'create'])->name('colocaciones.create');
    Route::post('/colocaciones', [ColocacionController::class, 'store'])->name('colocaciones.store');
    Route::get('/colocaciones/{colocacion}/edit', [ColocacionController::class, 'edit'])->name('colocaciones.edit');
    Route::put('/colocaciones/{colocacion}', [ColocacionController::class, 'update'])->name('colocaciones.update');
    Route::delete('/colocaciones/{colocacion}', [ColocacionController::class, 'destroy'])->name('colocaciones.destroy');

    // Contactos
    Route::get('/empresas/{empresa}/contactos/create', [App\Http\Controllers\ContactoController::class, 'create'])->name('contactos.create');
    Route::post('/empresas/{empresa}/contactos', [App\Http\Controllers\ContactoController::class, 'store'])->name('contactos.store');
    Route::get('/empresas/{empresa}/contactos/{contacto}/edit', [App\Http\Controllers\ContactoController::class, 'edit'])->name('contactos.edit');
    Route::put('/empresas/{empresa}/contactos/{contacto}', [App\Http\Controllers\ContactoController::class, 'update'])->name('contactos.update');
    Route::delete('/empresas/{empresa}/contactos/{contacto}', [App\Http\Controllers\ContactoController::class, 'destroy'])->name('contactos.destroy');
    // Personas de contacto
    Route::get('/empresas/{empresa}/personas-contacto/create', [App\Http\Controllers\PersonaContactoController::class, 'create'])->name('personas-contacto.create');
    Route::post('/empresas/{empresa}/personas-contacto', [App\Http\Controllers\PersonaContactoController::class, 'store'])->name('personas-contacto.store');
    Route::get('/empresas/{empresa}/personas-contacto/{persona}/edit', [App\Http\Controllers\PersonaContactoController::class, 'edit'])->name('personas-contacto.edit');
    Route::put('/empresas/{empresa}/personas-contacto/{persona}', [App\Http\Controllers\PersonaContactoController::class, 'update'])->name('personas-contacto.update');
    Route::delete('/empresas/{empresa}/personas-contacto/{persona}', [App\Http\Controllers\PersonaContactoController::class, 'destroy'])->name('personas-contacto.destroy');

    Route::get('/empresas/{empresa}/contactos/{contacto}/archivo', [App\Http\Controllers\ContactoController::class, 'descargarArchivo'])->name('contactos.archivo');

    // Valoraciones
    Route::get('/empresas/{empresa}/valoracion', [App\Http\Controllers\ValoracionController::class, 'createOrEdit'])->name('valoraciones.form');
    Route::post('/empresas/{empresa}/valoracion', [App\Http\Controllers\ValoracionController::class, 'store'])->name('valoraciones.store');
    Route::delete('/empresas/{empresa}/valoracion', [App\Http\Controllers\ValoracionController::class, 'destroy'])->name('valoraciones.destroy');
    
    // Informes y Exportaciones
    Route::get('/informes', [ExportController::class, 'informes'])->name('informes.index');
    Route::get('/export/empresas/excel', [ExportController::class, 'empresasExcel'])->name('export.empresas.excel');
    Route::get('/export/empresas/{empresa}/pdf', [ExportController::class, 'empresaPdf'])->name('export.empresa.pdf');
    Route::get('/export/colocaciones/excel', [ExportController::class, 'colocacionesExcel'])->name('export.colocaciones.excel');
    Route::get('/export/colocaciones/pdf', [ExportController::class, 'colocacionesPdf'])->name('export.colocaciones.pdf');
    
    // Administración (solo admin)
    Route::prefix('admin')->name('admin.')->middleware('can:admin')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->only(['index', 'show', 'edit', 'update']);
        Route::get('auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
        Route::get('auditoria/export/excel', [AuditoriaController::class, 'exportExcel'])->name('auditoria.export.excel');
        Route::get('auditoria/export/pdf', [AuditoriaController::class, 'exportPdf'])->name('auditoria.export.pdf');
        
        // Configuración del curso activo
        Route::get('configuracion/curso', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'cursoActivo'])->name('configuracion.curso');
        Route::post('configuracion/curso', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'cambiarCurso'])->name('configuracion.cambiar-curso');
        Route::post('configuracion/avanzar-curso', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'avanzarCurso'])->name('configuracion.avanzar-curso');
    });

    // =========================================================================
    // GESTION FFE: Ciclos / Calendario FFE (admin o responsable_ffe)
    // =========================================================================
    Route::prefix('admin')->name('admin.')->middleware('can:gestionarFfe')->group(function () {
        Route::resource('ciclos', CicloFormativoController::class);
        Route::post('ciclos/{ciclo}/grupos', [GrupoController::class, 'store'])->name('ciclos.grupos.store');
        Route::patch('ciclos/{ciclo}/grupos/{grupo}/toggle', [GrupoController::class, 'toggleActivo'])->name('ciclos.grupos.toggle');

        Route::get('calendario-ffe', [\App\Http\Controllers\Admin\NoLectivoIesController::class, 'index'])->name('calendario-ffe.index');
        Route::post('calendario-ffe', [\App\Http\Controllers\Admin\NoLectivoIesController::class, 'store'])->name('calendario-ffe.store');
        Route::delete('calendario-ffe/{noLectivoIe}', [\App\Http\Controllers\Admin\NoLectivoIesController::class, 'destroy'])->name('calendario-ffe.destroy');
    });

    // =========================================================================
    // CURRICULUM: Modulos / RA / CE / Elegibles FFE
    // =========================================================================
    Route::prefix('admin/curriculum')->name('admin.curriculum.')->group(function () {
        // Lectura: admin, responsable_ffe, responsable_ciclo, profesor
        Route::middleware('can:verCurriculum')->group(function () {
            Route::get('/', [\App\Http\Controllers\CurriculumController::class, 'index'])->name('index');
            Route::get('/{ciclo}/modulos', [\App\Http\Controllers\ModuloProfesionalController::class, 'index'])->name('modulos.index');
            Route::get('/{ciclo}/modulos/{modulo}/edit', [\App\Http\Controllers\ModuloProfesionalController::class, 'edit'])->name('modulos.edit');
            Route::get('/modulos/{modulo}/ra', [\App\Http\Controllers\ResultadoAprendizajeController::class, 'index'])->name('ra.index');
        });

        // Escritura: admin, responsable_ffe
        Route::middleware('can:gestionarCurriculum')->group(function () {
            Route::post('/{ciclo}/modulos', [\App\Http\Controllers\ModuloProfesionalController::class, 'store'])->name('modulos.store');
            Route::put('/{ciclo}/modulos/{modulo}', [\App\Http\Controllers\ModuloProfesionalController::class, 'update'])->name('modulos.update');
            Route::delete('/{ciclo}/modulos/{modulo}', [\App\Http\Controllers\ModuloProfesionalController::class, 'destroy'])->name('modulos.destroy');

            Route::post('/modulos/{modulo}/ra', [\App\Http\Controllers\ResultadoAprendizajeController::class, 'store'])->name('ra.store');
            Route::put('/ra/{ra}', [\App\Http\Controllers\ResultadoAprendizajeController::class, 'update'])->name('ra.update');
            Route::delete('/ra/{ra}', [\App\Http\Controllers\ResultadoAprendizajeController::class, 'destroy'])->name('ra.destroy');

            Route::post('/ra/{ra}/criterios', [\App\Http\Controllers\CriterioEvaluacionController::class, 'store'])->name('ce.store');
            Route::put('/criterios/{ce}', [\App\Http\Controllers\CriterioEvaluacionController::class, 'update'])->name('ce.update');
            Route::delete('/criterios/{ce}', [\App\Http\Controllers\CriterioEvaluacionController::class, 'destroy'])->name('ce.destroy');
        });

        // Elegibles: autorizacion propia via Gate gestionarElegibles en el controller, sin middleware de ruta
        Route::get('/elegibles', [\App\Http\Controllers\ElegibleFfeController::class, 'index'])->name('elegibles.index');
        Route::post('/elegibles/toggle', [\App\Http\Controllers\ElegibleFfeController::class, 'toggle'])->name('elegibles.toggle');
        Route::post('/elegibles/toggle-ce', [\App\Http\Controllers\ElegibleFfeController::class, 'toggleCe'])->name('elegibles.toggle-ce');
    });

    // =========================================================================
    // ALUMNOS
    // =========================================================================
    Route::get('alumnos/archivo', [\App\Http\Controllers\AlumnoController::class, 'archivo'])->name('alumnos.archivo');
    Route::get('alumnos/import', [\App\Http\Controllers\AlumnoController::class, 'importForm'])->name('alumnos.import');
    Route::post('alumnos/import', [\App\Http\Controllers\AlumnoController::class, 'import'])->name('alumnos.import.submit');
    Route::get('alumnos/plantilla', [\App\Http\Controllers\AlumnoController::class, 'descargarPlantilla'])->name('alumnos.plantilla');
    Route::resource('alumnos', \App\Http\Controllers\AlumnoController::class)->except(['destroy']);
    Route::delete('alumnos/{alumno}', [\App\Http\Controllers\AlumnoController::class, 'destroy'])->name('alumnos.destroy');
    Route::post('alumnos/{alumno}/resetear-password', [\App\Http\Controllers\AlumnoController::class, 'resetearPassword'])->name('alumnos.resetear-password');
    
    // =========================================================================
    // ASIGNACIONES FFE
    // =========================================================================
    Route::get('alumnos/{alumno}/asignaciones/create', [\App\Http\Controllers\AsignacionFctController::class, 'create'])->name('asignaciones.create');
    Route::post('alumnos/{alumno}/asignaciones', [\App\Http\Controllers\AsignacionFctController::class, 'store'])->name('asignaciones.store');
    Route::get('asignaciones/{asignacion}', [\App\Http\Controllers\AsignacionFctController::class, 'show'])->name('asignaciones.show');
    Route::get('asignaciones/{asignacion}/edit', [\App\Http\Controllers\AsignacionFctController::class, 'edit'])->name('asignaciones.edit');
    Route::put('asignaciones/{asignacion}', [\App\Http\Controllers\AsignacionFctController::class, 'update'])->name('asignaciones.update');
    Route::post('asignaciones/{asignacion}/cancelar', [\App\Http\Controllers\AsignacionFctController::class, 'cancelar'])->name('asignaciones.cancelar');

    // Endpoints JSON auxiliares para selects dinámicos (Alpine.js)
    Route::get('interna/empresas/{empresa}/sedes', [\App\Http\Controllers\AsignacionFctController::class, 'sedes'])->name('interna.empresa.sedes');
    Route::get('interna/empresas/{empresa}/contactos', [\App\Http\Controllers\AsignacionFctController::class, 'contactos'])->name('interna.empresa.contactos');



    // =========================================================================
    // DOCUMENTOS FCT
    // =========================================================================
    Route::get("asignaciones/{asignacion}/documentos/plan-formativo/configurar", [\App\Http\Controllers\PlanFormativoController::class, "form"])->name("documentos.plan-formativo.form");
    Route::post("asignaciones/{asignacion}/documentos/plan-formativo/generar", [\App\Http\Controllers\PlanFormativoController::class, "generar"])->name("documentos.plan-formativo.generar");
    Route::post("asignaciones/{asignacion}/documentos/{tipo}/generar",  [\App\Http\Controllers\DocumentoFctController::class, "generar"])->name("documentos.generar");
    Route::get("documentos/{documento}/descargar",                      [\App\Http\Controllers\DocumentoFctController::class, "descargar"])->name("documentos.descargar");
    Route::post("asignaciones/{asignacion}/documentos/subir-firmado",   [\App\Http\Controllers\DocumentoFctController::class, "subirFirmado"])->name("documentos.subir-firmado");
    Route::delete("documentos/{documento}",                             [\App\Http\Controllers\DocumentoFctController::class, "destroy"])->name("documentos.destroy");

    // =========================================================================
    // CUADERNO DIGITAL — seguimientos IES
    // =========================================================================
    Route::get("asignaciones/{asignacion}/seguimientos",                              [\App\Http\Controllers\SeguimientoIesController::class, "index"])->name("asignaciones.seguimientos.index");
    Route::post("asignaciones/{asignacion}/seguimientos/{seguimiento}/confirmar",     [\App\Http\Controllers\SeguimientoIesController::class, "confirmar"])->name("asignaciones.seguimientos.confirmar");
    Route::post("asignaciones/{asignacion}/ajustes-horas-semana",                    [\App\Http\Controllers\SeguimientoIesController::class, "ajustarHorasSemana"])->name("asignaciones.ajustes-horas-semana.store");
    Route::post("asignaciones/{asignacion}/marcar-no-trabajado",                     [\App\Http\Controllers\SeguimientoIesController::class, "marcarNoTrabajado"])->name("asignaciones.marcar-no-trabajado.store");

    // Calendario asignacion
    Route::get("asignaciones/{asignacion}/calendario",                               [\App\Http\Controllers\CalendarioAsignacionController::class, "index"])->name("asignaciones.calendario.index");
    Route::post("asignaciones/{asignacion}/calendario",                              [\App\Http\Controllers\CalendarioAsignacionController::class, "store"])->name("asignaciones.calendario.store");
    Route::delete("asignaciones/{asignacion}/calendario/{calendario}",               [\App\Http\Controllers\CalendarioAsignacionController::class, "destroy"])->name("asignaciones.calendario.destroy");

    // Token tutor empresa
    Route::post("asignaciones/{asignacion}/token-tutor",                             [\App\Http\Controllers\TokenTutorEmpresaController::class, "generar"])->name("asignaciones.token-tutor.generar");
});

// =========================================================================
// ACCESO PUBLICO TUTOR EMPRESA (sin guard)
// =========================================================================
Route::middleware("throttle:tutor")->group(function () {
    Route::get("tutor/{token}",                                    [\App\Http\Controllers\TutorEmpresaController::class, "acceso"])->name("tutor.acceso");
    Route::post("tutor/{token}/comentar/{seguimiento}",            [\App\Http\Controllers\TutorEmpresaController::class, "comentar"])->name("tutor.comentar");
});
