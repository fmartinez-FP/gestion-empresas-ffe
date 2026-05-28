<?php

use Illuminate\Support\Facades\Schedule;

// Notificar convenios próximos a caducar (lunes 8:00, sept-junio)
Schedule::command('convenios:notificar')->weeklyOn(1, '08:00');

// Resumen semanal de asignaciones (lunes 8:30)
Schedule::command('asignaciones:notificar')->weeklyOn(1, '08:30');

// Resumen mensual (último viernes del mes a las 9:00, feb-mayo)
Schedule::command('resumen:mensual')->monthlyOn(1, '09:00')
    ->when(function () {
        // Ejecutar solo el último viernes del mes
        $hoy = now();
        $ultimoViernes = $hoy->copy()->endOfMonth()->previous('Friday');
        return $hoy->isSameDay($ultimoViernes);
    });

// Backup de base de datos (cada día a las 3:00)
Schedule::command('backup:database')->dailyAt('03:00');

// Limpiar notificaciones antiguas (> 60 días sin leer)
Schedule::call(function () {
    \App\Models\Notificacion::where('created_at', '<', now()->subDays(60))->delete();
})->dailyAt('04:00');

