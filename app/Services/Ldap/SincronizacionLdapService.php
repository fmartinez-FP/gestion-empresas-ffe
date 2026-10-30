<?php

namespace App\Services\Ldap;

use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Mantiene los usuarios del personal (los de LDAP) alineados con el servidor:
 *  - cuenta desactivada en LDAP o fuera del grupo de la aplicación -> inactivo;
 *  - cuenta borrada de LDAP -> se borra, salvo que haya registrado datos que
 *    se perderían (empresas, colocaciones...), en cuyo caso queda inactiva;
 *  - miembro del grupo de administradores -> rol admin; si deja de serlo -> profesor.
 *
 * Los alumnos y tutores de empresa no están en LDAP y no se tocan.
 */
class SincronizacionLdapService
{
    private const ROLES_FUERA_DE_LDAP = ['alumno', 'tutor_empresa'];

    /** Rol que conserva quien sale del grupo de administradores. */
    private const ROL_TRAS_DEJAR_DE_SER_ADMIN = 'profesor';

    /**
     * Tablas con datos creados por el usuario que desaparecerían (cascade) o
     * impedirían borrarlo (restrict).
     */
    private const DEPENDENCIAS = [
        'empresas'             => 'creador_id',
        'colocaciones'         => 'registrado_por_id',
        'asignaciones_fct'     => 'tutor_ies_id',
        'ajuste_horas_semana'  => 'created_by_id',
        'contactos'            => 'registrado_por_id',
        'valoraciones'         => 'valorado_por_id',
        'elegibles_ffe'        => 'created_by_id',
        'elegibles_ffe_ce'     => 'created_by_id',
    ];

    public function __construct(private DirectorioLdap $directorio)
    {
    }

    /**
     * @return array{desactivados: string[], reactivados: string[], eliminados: string[], conservados: string[], ascendidos: string[], degradados: string[], avisos: string[]}
     */
    public function sincronizar(bool $simular = false): array
    {
        $cuentas = $this->directorio->usuarios();

        // Un directorio vacío o sin administradores es un fallo de lectura, no
        // una baja masiva: ante la duda no se toca nada.
        if ($cuentas === []) {
            throw new RuntimeException('El directorio LDAP no ha devuelto ninguna cuenta; no se ha modificado nada.');
        }
        if (! in_array(true, array_column($cuentas, 'admin'), true)) {
            throw new RuntimeException('El grupo de administradores del LDAP está vacío o no se puede leer; no se ha modificado nada.');
        }

        $resultado = [
            'desactivados' => [], 'reactivados' => [], 'eliminados' => [],
            'conservados' => [], 'ascendidos' => [], 'degradados' => [], 'avisos' => [],
        ];
        $administradores = $this->idsAdministradores();

        $usuarios = User::whereNotIn('rol', self::ROLES_FUERA_DE_LDAP)->orderBy('username')->get();

        foreach ($usuarios as $usuario) {
            $cuenta = $cuentas[mb_strtolower($usuario->username)] ?? null;

            if ($cuenta === null) {
                $this->tratarBorrado($usuario, $simular, $administradores, $resultado);
                continue;
            }

            $debeEstarActivo = $cuenta['activo'] && $cuenta['en_ffe'];
            if ($usuario->activo !== $debeEstarActivo) {
                if (! $simular) {
                    $usuario->update(['activo' => $debeEstarActivo]);
                    $this->auditar($usuario, $debeEstarActivo ? 'Reactivado' : 'Desactivado');
                }
                $resultado[$debeEstarActivo ? 'reactivados' : 'desactivados'][] = $usuario->username;
            }

            $this->ajustarRolAdmin($usuario, $cuenta['admin'], $simular, $administradores, $resultado);
        }

        return $resultado;
    }

    /**
     * Al iniciar sesión: el rol de administrador sigue al grupo LDAP sin
     * esperar a la sincronización periódica.
     */
    public function refrescarRol(User $usuario): void
    {
        if (in_array($usuario->rol, self::ROLES_FUERA_DE_LDAP, true)) {
            return;
        }

        $cuenta = $this->directorio->usuario($usuario->username);
        if ($cuenta === null) {
            return;
        }

        $administradores = $this->idsAdministradores();
        $descartado = [];
        $this->ajustarRolAdmin($usuario, $cuenta['admin'], false, $administradores, $descartado);
    }

    private function tratarBorrado(User $usuario, bool $simular, array &$administradores, array &$resultado): void
    {
        $motivo = $this->datosAsociados($usuario);

        if ($motivo === null && $this->esUltimoAdministrador($usuario, $administradores)) {
            $motivo = 'es el único administrador de la aplicación';
        }

        if ($motivo !== null) {
            $resultado['conservados'][] = "{$usuario->username} ({$motivo})";
            if ($usuario->activo) {
                if (! $simular) {
                    $usuario->update(['activo' => false]);
                    $this->auditar($usuario, "Desactivado: ya no existe en LDAP y no se borra porque {$motivo}");
                }
                $resultado['desactivados'][] = $usuario->username;
            }
            return;
        }

        if (! $simular) {
            $this->auditar($usuario, 'Eliminado: ya no existe en LDAP');
            DB::transaction(fn () => $usuario->delete());
        }
        $administradores = array_values(array_diff($administradores, [$usuario->id]));
        $resultado['eliminados'][] = $usuario->username;
    }

    private function ajustarRolAdmin(User $usuario, bool $esAdminLdap, bool $simular, array &$administradores, array &$resultado): void
    {
        if ($esAdminLdap && ! $usuario->esAdmin()) {
            if (! $simular) {
                $this->cambiarRol($usuario, 'admin');
                $this->auditar($usuario, 'Rol admin concedido por el grupo de administradores de LDAP');
            }
            $administradores[] = $usuario->id;
            $resultado['ascendidos'][] = $usuario->username;
        } elseif (! $esAdminLdap && $usuario->esAdmin()) {
            if ($this->esUltimoAdministrador($usuario, $administradores)) {
                $resultado['avisos'][] = "{$usuario->username} ya no está en el grupo de administradores de LDAP, pero es el único administrador de la aplicación y se mantiene.";
                return;
            }
            if (! $simular) {
                $this->cambiarRol($usuario, self::ROL_TRAS_DEJAR_DE_SER_ADMIN);
                $this->auditar($usuario, 'Rol admin retirado: ya no está en el grupo de administradores de LDAP');
            }
            $administradores = array_values(array_diff($administradores, [$usuario->id]));
            $resultado['degradados'][] = $usuario->username;
        }
    }

    private function cambiarRol(User $usuario, string $rol): void
    {
        $usuario->update(['rol' => $rol, 'ciclo_id' => null]);
        $usuario->ciclos()->detach();
        $usuario->gruposTutor()->detach();
    }

    /** @return int[] */
    private function idsAdministradores(): array
    {
        return User::where('rol', 'admin')->where('activo', true)->pluck('id')->all();
    }

    private function esUltimoAdministrador(User $usuario, array $administradores): bool
    {
        return in_array($usuario->id, $administradores, true) && count($administradores) <= 1;
    }

    /** Descripción de los datos que dejaría atrás al borrarlo, o null si no hay. */
    private function datosAsociados(User $usuario): ?string
    {
        $con = [];
        foreach (self::DEPENDENCIAS as $tabla => $columna) {
            if (DB::table($tabla)->where($columna, $usuario->id)->exists()) {
                $con[] = $tabla;
            }
        }

        return $con === [] ? null : 'tiene datos asociados: ' . implode(', ', $con);
    }

    private function auditar(User $usuario, string $descripcion): void
    {
        Auditoria::create([
            'user_id'          => null,
            'user_nombre'      => 'Sincronización LDAP',
            'modelo'           => 'User',
            'modelo_id'        => $usuario->id,
            'accion'           => 'sincronizacion_ldap',
            'descripcion'      => "{$usuario->username}: {$descripcion}",
            'datos_anteriores' => null,
            'datos_nuevos'     => null,
            'ip'               => null,
        ]);
    }
}
