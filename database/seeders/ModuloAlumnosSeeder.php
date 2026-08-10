<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\AsignacionFct;
use App\Models\CicloFormativo;
use App\Models\Configuracion;
use App\Models\CriterioEvaluacion;
use App\Models\Direccion;
use App\Models\ElegibleFfe;
use App\Models\ElegibleFfeCe;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\ModuloProfesional;
use App\Models\PersonaContacto;
use App\Models\ResultadoAprendizaje;
use App\Models\SeguimientoDiario;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder de datos ficticios para pruebas visuales del módulo de Alumnos FFE.
 *
 * NO se ejecuta automáticamente desde DatabaseSeeder — lanzar a mano con:
 *   php artisan db:seed --class=ModuloAlumnosSeeder
 *
 * Cubre 3 niveles (básica/media/superior) x 2 cursos, con asignaciones por
 * debajo, en, y por encima del umbral legal de horas de cada combinación:
 * básica 150h, media 1º 250h / 2º 400h, superior 1º 350h / 2º 500h.
 *
 * También genera un currículum ficticio mínimo (módulos, RA, CE, elegibles
 * para el curso activo) por cada ciclo formativo, y sincroniza 1-2 RA (con
 * sus CE) en cada asignación FFE creada — necesario porque sin currículum
 * cargado no hay nada que mostrar en el selector de RA/CE de
 * asignaciones/create ni en asignaciones/show.
 *
 * IMPORTANTE: num_horas y horario se fijan directamente (fallback), porque
 * HorarioAsignacionService y la tabla horario_asignacion (Fase 9) no existen
 * en este repo — ver hallazgo 2026-08-05, Plan de Reconstrucción, nueva Fase D.
 * Cuando se reconstruya Fase 9 este seeder deberá reescribirse para generar
 * horario_asignacion real y dejar que el servicio calcule num_horas.
 */
class ModuloAlumnosSeeder extends Seeder
{
    private const UMBRALES = [
        ['nivel' => 'basica',   'curso' => 1, 'umbral' => 150],
        ['nivel' => 'basica',   'curso' => 2, 'umbral' => 150],
        ['nivel' => 'media',    'curso' => 1, 'umbral' => 250],
        ['nivel' => 'media',    'curso' => 2, 'umbral' => 400],
        ['nivel' => 'superior', 'curso' => 1, 'umbral' => 350],
        ['nivel' => 'superior', 'curso' => 2, 'umbral' => 500],
    ];

    public function run(): void
    {
        if (CicloFormativo::count() === 0) {
            $this->command->error('No hay ciclos formativos. Ejecuta primero: php artisan db:seed --class=CicloFormativoSeeder antes de este seeder.');
            return;
        }

        $cursoActivo = Configuracion::cursoActivo() ?? '2025-2026';

        $this->command->info('Sembrando datos ficticios del módulo de Alumnos FFE...');

        $profesores = $this->crearProfesores();
        $empresas   = $this->crearEmpresas($profesores->first());

        $this->command->info('Sembrando currículum ficticio (módulos/RA/CE/elegibles)...');
        $curriculum = $this->crearCurriculum($cursoActivo, $profesores->first());

        $totalAlumnos = 0;
        $totalAsignaciones = 0;

        foreach (self::UMBRALES as $combo) {
            $ciclo = CicloFormativo::where('nivel', $combo['nivel'])->inRandomOrder()->first();

            if (!$ciclo) {
                $this->command->warn("Sin ciclo formativo de nivel '{$combo['nivel']}' — saltando curso {$combo['curso']}.");
                continue;
            }

            $variantes = [
                'debajo' => $combo['umbral'] - rand(15, 40),
                'igual'  => $combo['umbral'],
                'encima' => $combo['umbral'] + rand(15, 50),
            ];

            foreach ($variantes as $etiqueta => $horas) {
                $alumno = Alumno::create([
                    'nombre'          => fake()->firstName(),
                    'apellidos'       => fake()->lastName() . ' ' . fake()->lastName(),
                    'email'           => fake()->unique()->safeEmail(),
                    'telefono'        => fake()->numerify('6########'),
                    'grupo_id'        => $this->resolverGrupo($ciclo->id, $combo['curso'])->id,
                    'curso_academico' => $cursoActivo,
                    'importado_via'   => 'manual',
                ]);
                $totalAlumnos++;

                $empresa = $empresas->random();

                $asignacion = AsignacionFct::create([
                    'alumno_id'        => $alumno->id,
                    'empresa_id'       => $empresa->id,
                    'sede_id'          => $empresa->direcciones->first()?->id,
                    'tutor_empresa_id' => $empresa->personasContacto->first()?->id,
                    'tutor_ies_id'     => $profesores->random()->id,
                    'ciclo_id'         => $ciclo->id,
                    'curso_academico'  => $cursoActivo,
                    'numero_curso'     => $combo['curso'],
                    'fecha_inicio'     => '2025-09-15',
                    'fecha_fin'        => '2026-06-15',
                    'num_horas'        => max($horas, 0),
                    'horario'          => 'Lunes a viernes de 9:00 a 14:00 (dato ficticio — Fase 9 pendiente de reconstrucción)',
                    'estado'           => 'activa',
                ]);
                $totalAsignaciones++;

                $this->asignarRaCe($asignacion, $curriculum);

                $this->command->line("  · {$ciclo->codigo} {$combo['curso']}º ({$etiqueta}): {$horas}h — umbral {$combo['umbral']}h");
            }
        }

        $totalAsignaciones += $this->crearAsignacionesEstadoVariado($cursoActivo, $empresas, $profesores, $curriculum);
        $totalAlumnos += $this->crearAlumnosSinAsignacion($cursoActivo);

        $this->crearAlumnoPortalPrueba($cursoActivo, $empresas, $profesores, $curriculum);
        $totalAlumnos++;
        $totalAsignaciones++;

        $totalSeguimientos = $this->crearSeguimientosDigitales();

        $this->command->info("✓ Sembrados {$totalAlumnos} alumnos y {$totalAsignaciones} asignaciones ficticias.");
        $this->command->info("✓ Sembrados {$totalSeguimientos} registros de cuaderno digital (seguimiento_diario).");
    }

    private function crearProfesores()
    {
        $existentes = User::whereIn('rol', ['profesor', 'responsable_ciclo', 'responsable_ffe', 'admin'])->get();

        if ($existentes->count() >= 3) {
            return $existentes;
        }

        for ($i = $existentes->count(); $i < 3; $i++) {
            User::factory()->create([
                'nombre' => 'Profesor de Prueba ' . ($i + 1),
                'rol'    => 'profesor',
            ]);
        }

        return User::whereIn('rol', ['profesor', 'responsable_ciclo', 'responsable_ffe', 'admin'])->get();
    }

    /**
     * Reutiliza un grupo existente para ese ciclo+curso si ya lo hay (p.ej.
     * uno de los grupos reales creados en el punto 6 de la Fase L), o crea
     * uno nuevo sobre la marcha si no existe -- igual que crearProfesores()
     * reutiliza usuarios ya sembrados en vez de duplicar.
     */
    private function resolverGrupo(int $cicloId, int $numeroCurso): Grupo
    {
        return Grupo::firstOrCreate(
            ['ciclo_id' => $cicloId, 'numero_curso' => $numeroCurso],
            ['etiqueta' => 'A', 'activo' => true]
        );
    }

    private function crearEmpresas(User $creador)
    {
        $nombres = [
            'Talleres Ficticios del Sur S.L.',
            'Sistemas Demo Norte S.A.',
            'Electrónica de Prueba Madrid S.L.',
            'Instalaciones Ejemplo Centro S.L.',
            'Redes y Datos de Mentira S.A.',
        ];

        $empresas = collect();

        foreach ($nombres as $nombre) {
            $empresa = Empresa::create([
                'nombre'       => $nombre,
                'cif'          => strtoupper(fake()->unique()->bothify('B########')),
                'num_convenio' => 'CONV-DEMO-' . fake()->unique()->numerify('###'),
                'fecha_firma'  => fake()->dateTimeBetween('-3 years', '-6 months'),
                'creador_id'   => $creador->id,
                'notas'        => 'Empresa ficticia generada por ModuloAlumnosSeeder — no usar en producción.',
            ]);

            Direccion::create([
                'empresa_id'    => $empresa->id,
                'tipo_via'      => 'Calle',
                'nombre_via'    => fake()->streetName(),
                'numero'        => (string) fake()->buildingNumber(),
                'codigo_postal' => fake()->numerify('28###'),
                'municipio'     => fake()->randomElement(['Madrid', 'Alcobendas', 'Getafe', 'Leganés']),
                'principal'     => true,
            ]);

            PersonaContacto::create([
                'empresa_id' => $empresa->id,
                'nombre'     => fake()->name(),
                'cargo'      => fake()->randomElement(['Responsable de RRHH', 'Tutor de prácticas', 'Encargado de taller']),
                'telefono'   => fake()->numerify('9########'),
                'email'      => fake()->unique()->safeEmail(),
                'notas'      => null,
                'principal'  => true,
            ]);

            $ciclosAleatorios = CicloFormativo::inRandomOrder()->limit(rand(2, 4))->pluck('id');
            $syncData = [];
            foreach ($ciclosAleatorios as $cicloId) {
                $syncData[$cicloId] = ['acepta_primero' => (bool) rand(0, 1), 'acepta_segundo' => true];
            }
            $empresa->ciclos()->sync($syncData);

            $empresas->push($empresa->fresh(['direcciones', 'personasContacto']));
        }

        return $empresas;
    }

    /**
     * Crea 2 módulos (1º y 2º curso) por cada ciclo formativo, 2 RA por
     * módulo, 2 CE por RA, y marca todos los RA/CE como elegibles para el
     * curso académico activo. Devuelve una estructura indexada por
     * [ciclo_id][numero_curso] => Collection<ResultadoAprendizaje> (con
     * 'criterios' cargado) para poder asignarlos luego a las AsignacionFct.
     */
    private function crearCurriculum(string $cursoActivo, User $creador): array
    {
        $estructura = [];

        foreach (CicloFormativo::all() as $ciclo) {
            $estructura[$ciclo->id] = [1 => collect(), 2 => collect()];

            foreach ([1, 2] as $numeroCurso) {
                $modulo = ModuloProfesional::create([
                    'ciclo_id'      => $ciclo->id,
                    'codigo'        => $ciclo->codigo . '-M' . $numeroCurso,
                    'nombre'        => "Módulo ficticio {$numeroCurso}º — {$ciclo->nombre}",
                    'horas_totales' => 300,
                    'curso'         => $numeroCurso,
                ]);

                for ($i = 1; $i <= 2; $i++) {
                    $ra = ResultadoAprendizaje::create([
                        'modulo_id'               => $modulo->id,
                        'codigo'                  => "RA{$i}",
                        'descripcion'             => "Resultado de aprendizaje ficticio {$i} del módulo {$modulo->codigo}.",
                        'activo_ffe'              => true,
                        'curso_academico_activo'  => $cursoActivo,
                    ]);

                    ElegibleFfe::create([
                        'resultado_aprendizaje_id' => $ra->id,
                        'curso_academico'          => $cursoActivo,
                        'created_by_id'             => $creador->id,
                    ]);

                    for ($j = 1; $j <= 2; $j++) {
                        $ce = CriterioEvaluacion::create([
                            'resultado_aprendizaje_id' => $ra->id,
                            'codigo'                    => "CE{$i}.{$j}",
                            'descripcion'                => "Criterio de evaluación ficticio {$i}.{$j} de {$ra->codigo}.",
                        ]);

                        ElegibleFfeCe::create([
                            'criterio_evaluacion_id' => $ce->id,
                            'curso_academico'         => $cursoActivo,
                            'created_by_id'            => $creador->id,
                        ]);
                    }

                    $estructura[$ciclo->id][$numeroCurso]->push($ra->load('criterios'));
                }
            }
        }

        return $estructura;
    }

    /**
     * Sincroniza hasta 2 RA (y todos sus CE) elegibles para el ciclo/curso
     * de la asignación dada. No hace nada si no hay currículum para esa
     * combinación (ej. ciclo sin módulos sembrados).
     */
    private function asignarRaCe(AsignacionFct $asignacion, array $curriculum): void
    {
        $raDisponibles = $curriculum[$asignacion->ciclo_id][$asignacion->numero_curso] ?? collect();

        if ($raDisponibles->isEmpty()) {
            return;
        }

        $raSeleccionados = $raDisponibles->take(2);
        $ceSeleccionados = $raSeleccionados->flatMap(fn($ra) => $ra->criterios->pluck('id'));

        $asignacion->resultadosAprendizaje()->sync($raSeleccionados->pluck('id'));
        $asignacion->criteriosEvaluacion()->sync($ceSeleccionados);
    }

    private function crearAsignacionesEstadoVariado(string $cursoActivo, $empresas, $profesores, array $curriculum): int
    {
        $ciclo = CicloFormativo::inRandomOrder()->first();
        $contador = 0;

        for ($i = 0; $i < 2; $i++) {
            $numeroCurso = fake()->randomElement([1, 2]);
            $alumno = Alumno::create([
                'nombre' => fake()->firstName(), 'apellidos' => fake()->lastName() . ' ' . fake()->lastName(),
                'email' => fake()->unique()->safeEmail(), 'telefono' => fake()->numerify('6########'),
                'grupo_id' => $this->resolverGrupo($ciclo->id, $numeroCurso)->id, 'curso_academico' => $cursoActivo,
                'importado_via' => 'manual',
            ]);
            $empresa = $empresas->random();
            $asignacion = AsignacionFct::create([
                'alumno_id' => $alumno->id, 'empresa_id' => $empresa->id,
                'sede_id' => $empresa->direcciones->first()?->id,
                'tutor_empresa_id' => $empresa->personasContacto->first()?->id,
                'tutor_ies_id' => $profesores->random()->id, 'ciclo_id' => $ciclo->id,
                'curso_academico' => $cursoActivo, 'numero_curso' => $alumno->numero_curso,
                'fecha_inicio' => '2025-09-15', 'fecha_fin' => '2026-01-20',
                'num_horas' => rand(50, 150), 'horario' => 'Horario ficticio (cancelada antes de finalizar)',
                'estado' => 'cancelada', 'motivo_baja' => 'Baja voluntaria del alumno (dato ficticio de prueba)',
            ]);
            $this->asignarRaCe($asignacion, $curriculum);
            $contador++;
        }

        for ($i = 0; $i < 2; $i++) {
            $alumno = Alumno::create([
                'nombre' => fake()->firstName(), 'apellidos' => fake()->lastName() . ' ' . fake()->lastName(),
                'email' => fake()->unique()->safeEmail(), 'telefono' => fake()->numerify('6########'),
                'grupo_id' => $this->resolverGrupo($ciclo->id, 2)->id, 'curso_academico' => '2024-2025',
                'importado_via' => 'manual',
            ]);
            $empresa = $empresas->random();
            $asignacion = AsignacionFct::create([
                'alumno_id' => $alumno->id, 'empresa_id' => $empresa->id,
                'sede_id' => $empresa->direcciones->first()?->id,
                'tutor_empresa_id' => $empresa->personasContacto->first()?->id,
                'tutor_ies_id' => $profesores->random()->id, 'ciclo_id' => $ciclo->id,
                'curso_academico' => '2024-2025', 'numero_curso' => 2,
                'fecha_inicio' => '2024-09-15', 'fecha_fin' => '2025-06-15',
                'num_horas' => rand(350, 420), 'horario' => 'Horario ficticio (curso ya finalizado)',
                'estado' => 'finalizada',
            ]);
            // Nota: el currículum solo se marcó elegible para $cursoActivo, no para
            // '2024-2025', así que asignarRaCe no encontrará RA elegibles aquí y no
            // sincronizará nada — es el comportamiento correcto y esperado.
            $this->asignarRaCe($asignacion, $curriculum);

            // Genera el histórico de colocaciones para esta asignación ya finalizada
            // (sesion 2026-08-10): en el flujo real esto lo dispara el comando
            // ffe:reset-curso, pero el seeder crea la asignación ya finalizada
            // directamente sin pasar por el comando, así que sin esta llamada nunca
            // se generaría historial para estos datos ficticios de curso pasado.
            app(\App\Services\HistorialAsignacionService::class)->registrar($asignacion);

            $contador++;
        }

        return $contador;
    }

    private function crearAlumnosSinAsignacion(string $cursoActivo): int
    {
        $ciclo = CicloFormativo::inRandomOrder()->first();

        for ($i = 0; $i < 2; $i++) {
            $numeroCurso = fake()->randomElement([1, 2]);
            Alumno::create([
                'nombre' => fake()->firstName(), 'apellidos' => fake()->lastName() . ' ' . fake()->lastName(),
                'email' => fake()->unique()->safeEmail(), 'telefono' => fake()->numerify('6########'),
                'grupo_id' => $this->resolverGrupo($ciclo->id, $numeroCurso)->id, 'curso_academico' => $cursoActivo,
                'importado_via' => 'manual',
            ]);
        }

        return 2;
    }

    private function crearAlumnoPortalPrueba(string $cursoActivo, $empresas, $profesores, array $curriculum): void
    {
        $ciclo = CicloFormativo::where('nivel', 'media')->first() ?? CicloFormativo::first();

        $user = User::create([
            'username' => 'alumno.prueba',
            'nombre'   => 'Alumno de Prueba (portal)',
            'email'    => 'fmartinezmarti@gmail.com',
            'password' => Hash::make('cambiame123'),
            'rol'      => 'alumno',
            'activo'   => true,
        ]);
        // password_change_required NO está en $fillable de User.php actualmente
        // (hallazgo 2026-08-05 — ver nota de sesión). Se fija por asignación
        // directa de atributo, sin depender de mass assignment.
        $user->password_change_required = true;
        $user->save();

        $alumno = Alumno::create([
            'user_id'         => $user->id,
            'nombre'          => 'Alumno de Prueba',
            'apellidos'       => 'Portal Testing',
            'email'           => 'fmartinezmarti@gmail.com',
            'telefono'        => fake()->numerify('6########'),
            'grupo_id'        => $this->resolverGrupo($ciclo->id, 2)->id,
            'curso_academico' => $cursoActivo,
            'importado_via'   => 'manual',
        ]);

        $empresa = $empresas->random();

        $asignacion = AsignacionFct::create([
            'alumno_id'        => $alumno->id,
            'empresa_id'       => $empresa->id,
            'sede_id'          => $empresa->direcciones->first()?->id,
            'tutor_empresa_id' => $empresa->personasContacto->first()?->id,
            'tutor_ies_id'     => $profesores->first()->id,
            'ciclo_id'         => $ciclo->id,
            'curso_academico'  => $cursoActivo,
            'numero_curso'     => 2,
            'fecha_inicio'     => '2025-09-15',
            'fecha_fin'        => '2026-06-15',
            'num_horas'        => 380,
            'horario'          => 'Lunes a viernes de 9:00 a 14:00 (dato ficticio — Fase 9 pendiente)',
            'estado'           => 'activa',
        ]);

        $this->asignarRaCe($asignacion, $curriculum);

        $this->command->info('✓ Alumno de prueba para portal — usuario: alumno.prueba / contraseña: cambiame123 (email: fmartinezmarti@gmail.com)');
    }

    /**
     * Genera hasta 8 entradas de cuaderno digital por asignación activa,
     * en días laborables desde fecha_inicio, alternando confirmadas/pendientes
     * para poder ver ambos estados en la UI del tutor y del alumno.
     */
    private function crearSeguimientosDigitales(): int
    {
        $asignacionesActivas = AsignacionFct::where('estado', 'activa')->get();
        $total = 0;

        foreach ($asignacionesActivas as $asignacion) {
            if (!$asignacion->fecha_inicio) {
                continue;
            }

            $fecha     = \Carbon\Carbon::parse($asignacion->fecha_inicio);
            $fechaFin  = $asignacion->fecha_fin
                ? \Carbon\Carbon::parse($asignacion->fecha_fin)
                : $fecha->copy()->addMonths(9);
            $creados = 0;
            $dia     = 0;

            while ($creados < 8 && $fecha->lte($fechaFin)) {
                if (!$fecha->isWeekend()) {
                    $confirmado = $dia % 3 !== 0; // ~2 de cada 3 confirmadas

                    SeguimientoDiario::create([
                        'asignacion_id'      => $asignacion->id,
                        'fecha'              => $fecha->toDateString(),
                        'descripcion_tareas' => fake()->sentence(12),
                        'evidencia_path'     => null,
                        'hora_entrada'       => '09:00:00',
                        'hora_salida'        => '14:00:00',
                        'confirmado_tutor'   => $confirmado,
                        'confirmado_at'      => $confirmado ? $fecha->copy()->addDay() : null,
                        'comentario_tutor'   => $confirmado ? fake()->optional(0.4)->sentence() : null,
                    ]);

                    $creados++;
                    $total++;
                }
                $dia++;
                $fecha->addDay();
            }
        }

        return $total;
    }
}
