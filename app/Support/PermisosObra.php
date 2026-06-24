<?php

namespace App\Support;

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Matriz de permisos por rol de obra, configurable por el Admin.
 *
 * - El Admin de plataforma (rol global administrativo) puede todo, siempre.
 * - El resto depende de su rol DENTRO de la obra y de la matriz guardada
 *   en la tabla `permisos_obra`.
 */
class PermisosObra
{
    private const CACHE_KEY = 'permisos_obra_map';

    /**
     * Catálogo de capacidades, agrupadas para la UI.
     *
     * @var array<string, array<string, string>>
     */
    public const CATALOGO = [
        'Documentos' => [
            'documento.ver' => 'Ver documentos',
            'documento.subir' => 'Subir documentos',
            'documento.eliminar' => 'Eliminar documentos',
            'carpeta.gestionar' => 'Gestionar carpetas',
        ],
        'Cuaderno de obra' => [
            'cuaderno.ver' => 'Ver cuaderno',
            'cuaderno.escribir' => 'Escribir en el cuaderno',
            'cuaderno.eliminar' => 'Eliminar asientos',
        ],
        'Calendario' => [
            'calendario.ver' => 'Ver calendario',
            'calendario.gestionar' => 'Crear/editar eventos',
        ],
        'Equipo y obra' => [
            'equipo.gestionar' => 'Gestionar el equipo',
            'obra.editar' => 'Editar datos de la obra',
        ],
        'Caja chica' => [
            'caja.ver' => 'Ver caja chica',
            'caja.registrar' => 'Registrar movimientos',
            'caja.gestionar' => 'Gestionar caja (eliminar)',
        ],
    ];

    /**
     * @return array<int, string>
     */
    public static function permisosValidos(): array
    {
        return array_merge(...array_map('array_keys', array_values(self::CATALOGO)));
    }

    /**
     * Matriz guardada: rol => lista de permisos concedidos.
     *
     * @return array<string, array<int, string>>
     */
    public static function matriz(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => DB::table('permisos_obra')
            ->get()
            ->groupBy('rol_obra')
            ->map(fn ($filas) => $filas->pluck('permiso')->all())
            ->all());
    }

    /**
     * ¿El usuario puede realizar `$permiso` dentro de `$obra`?
     */
    public static function puede(User $user, Obra $obra, string $permiso): bool
    {
        if ($user->hasAnyRole(RolGlobal::rolesAdministrativos())) {
            return true;
        }

        $rol = self::rolEnObra($user, $obra);

        if ($rol === null) {
            return false;
        }

        return in_array($permiso, self::matriz()[$rol] ?? [], true);
    }

    /**
     * Rol del usuario dentro de la obra (o null si no pertenece).
     */
    public static function rolEnObra(User $user, Obra $obra): ?string
    {
        $miembro = $obra->usuarios()
            ->where('users.id', $user->id)
            ->first();

        return $miembro?->pivot->rol_obra;
    }

    /**
     * ¿El usuario pertenece a la obra (cualquier rol) o es admin?
     */
    public static function esMiembro(User $user, Obra $obra): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesAdministrativos())
            || self::rolEnObra($user, $obra) !== null;
    }

    /**
     * Estado completo para la UI: rol => [permiso => bool] sobre todo el catálogo.
     *
     * @return array<string, array<string, bool>>
     */
    public static function estadoCompleto(): array
    {
        $matriz = self::matriz();
        $permisos = self::permisosValidos();
        $estado = [];

        foreach (RolObra::values() as $rol) {
            $concedidos = $matriz[$rol] ?? [];

            foreach ($permisos as $permiso) {
                $estado[$rol][$permiso] = in_array($permiso, $concedidos, true);
            }
        }

        return $estado;
    }

    /**
     * Reescribe la matriz. $datos: rol => [permiso => bool].
     *
     * @param  array<string, array<string, bool>>  $datos
     */
    public static function sincronizar(array $datos): void
    {
        $roles = RolObra::values();
        $permisos = self::permisosValidos();
        $filas = [];

        foreach ($datos as $rol => $capacidades) {
            if (! in_array($rol, $roles, true)) {
                continue;
            }

            foreach ($capacidades as $permiso => $activo) {
                if ($activo && in_array($permiso, $permisos, true)) {
                    $filas[] = ['rol_obra' => $rol, 'permiso' => $permiso];
                }
            }
        }

        DB::transaction(function () use ($filas) {
            DB::table('permisos_obra')->truncate();

            if ($filas !== []) {
                DB::table('permisos_obra')->insert($filas);
            }
        });

        Cache::forget(self::CACHE_KEY);
    }
}
