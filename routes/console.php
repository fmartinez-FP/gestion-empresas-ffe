<?php

use Illuminate\Support\Facades\Schedule;

// Notificar convenios próximos a caducar (lunes 8:00, sept-junio)
Schedule::command('convenios:notificar')->weeklyOn(1, '08:00');

// Resumen semanal de asignaciones (lunes 8:30)
Schedule::command('asignaciones:notificar')->weeklyOn(1, '08:30');

// Resumen mensual (último viernes del mes a las 9:00)
// Antes: monthlyOn(1) + when(último viernes) => nunca coincidían (día 1 vs último viernes).
Schedule::command('resumen:mensual')->fridays()->at('09:00')
    ->when(function () {
        // Ejecutar solo el último viernes del mes
        return now()->copy()->addWeek()->month !== now()->month;
    });

// Avisos a tutores IES con seguimientos sin confirmar (respeta intervalo_email_tutor)
Schedule::command('ffe:notificar-tutores')->dailyAt('08:15');

// Usuarios al día con LDAP: bajas, borrados y rol de administrador (cada día a las 2:30)
Schedule::command('ffe:sincronizar-ldap')->dailyAt('02:30');

// Backup de base de datos (cada día a las 3:00)
Schedule::command('backup:database')->dailyAt('03:00');

// Limpiar notificaciones antiguas (> 60 días sin leer)
Schedule::call(function () {
    \App\Models\Notificacion::where('created_at', '<', now()->subDays(60))->delete();
})->dailyAt('04:00');

