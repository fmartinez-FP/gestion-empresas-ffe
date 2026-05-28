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
        Route::resource('ciclos', CicloFormativoController::class);
        Route::get('auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
        Route::get('auditoria/export/excel', [AuditoriaController::class, 'exportExcel'])->name('auditoria.export.excel');
        Route::get('auditoria/export/pdf', [AuditoriaController::class, 'exportPdf'])->name('auditoria.export.pdf');
        
        // Configuración del curso activo
        Route::get('configuracion/curso', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'cursoActivo'])->name('configuracion.curso');
        Route::post('configuracion/curso', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'cambiarCurso'])->name('configuracion.cambiar-curso');
        Route::post('configuracion/avanzar-curso', [\App\Http\Controllers\Admin\ConfiguracionController::class, 'avanzarCurso'])->name('configuracion.avanzar-curso');
    });
    
});
