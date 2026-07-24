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
 * - La matriz global (obra_id NULL) aplica a todas las obras por defecto;
 *   una obra puede tener matriz propia (obra_id = X) que la reemplaza.
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
     * Permisos de sólo lectura del catálogo. El rol global Gerente los tiene
     * concedidos en CUALQUIER obra sin estar en el pivot (visión global);
     * cualquier otra capacidad (escritura, gestión, eliminación) le está negada.
     *
     * @var array<int, string>
     */
    public const PERMISOS_LECTURA = [
        'documento.ver',
        'cuaderno.ver',
        'calendario.ver',
        'caja.ver',
    ];

    /**
     * Permisos reservados al rol de obra Administrador: no son concedibles a
     * ningún otro rol de obra desde la matriz (la UI ni los muestra). El
     * Gerente global conserva su lectura (`caja.ver`) por PERMISOS_LECTURA.
     *
     * @var array<int, string>
     */
    public const SOLO_ADMINISTRADOR = [
        'caja.ver',
        'caja.registrar',
        'caja.gestionar',
    ];

    /**
     * @return array<int, string>
     */
    public static function permisosValidos(): array
    {
        return array_merge(...array_map('array_keys', array_values(self::CATALOGO)));
    }

    /**
     * ¿El permiso es de sólo lectura (visible para el Gerente en toda obra)?
     */
    public static function esLectura(string $permiso): bool
    {
        return in_array($permiso, self::PERMISOS_LECTURA, true);
    }

    /**
     * ¿Puede concederse `$permiso` al rol de obra `$rol`?
     */
    public static function permitidoParaRol(string $permiso, string $rol): bool
    {
        return $rol === RolObra::Administrador->value
            || ! in_array($permiso, self::SOLO_ADMINISTRADOR, true);
    }

    /**
     * Mapa completo cacheado: obra_id (0 = matriz global) => rol => permisos.
     *
     * @return array<int, array<string, array<int, string>>>
     */
    private static function mapa(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => DB::table('permisos_obra')
            ->get()
            ->groupBy(fn ($fila) => (int) ($fila->obra_id ?? 0))
            ->map(fn ($filas) => $filas
                ->groupBy('rol_obra')
                ->map(fn ($grupo) => $grupo->pluck('permiso')->all())
                ->all())
            ->all());
    }

    /**
     * Matriz global (por defecto): rol => lista de permisos concedidos.
     *
     * @return array<string, array<int, string>>
     */
    public static function matriz(): array
    {
        return self::mapa()[0] ?? [];
    }

    /**
     * Matriz efectiva para una obra: la propia si existe, si no la global.
     *
     * @return array<string, array<int, string>>
     */
    public static function matrizPara(Obra $obra): array
    {
        return self::mapa()[$obra->id] ?? self::matriz();
    }

    /**
     * ¿La obra tiene matriz de permisos personalizada?
     */
    public static function tieneMatrizPropia(Obra $obra): bool
    {
        return isset(self::mapa()[$obra->id]);
    }

    /**
     * ¿El usuario puede realizar `$permiso` dentro de `$obra`?
     */
    public static function puede(User $user, Obra $obra, string $permiso): bool
    {
        if ($user->hasAnyRole(RolGlobal::rolesAdministrativos())) {
            return true;
        }

        // Gerente (visor global): lectura de cualquier obra, nunca escritura.
        if ($user->hasRole(RolGlobal::Gerente->value)) {
            return self::esLectura($permiso);
        }

        $rol = self::rolEnObra($user, $obra);

        if ($rol === null) {
            return false;
        }

        // Los permisos reservados (caja chica) nunca aplican a otros roles,
        // aunque una matriz antigua aún tuviera la fila en BD.
        if (! self::permitidoParaRol($permiso, $rol)) {
            return false;
        }

        return in_array($permiso, self::matrizPara($obra)[$rol] ?? [], true);
    }

    /**
     * ¿El usuario es Administrador dentro de esta obra concreta?
     * (o Admin de plataforma, que administra cualquier obra).
     */
    public static function esAdministradorDeObra(User $user, Obra $obra): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesAdministrativos())
            || self::rolEnObra($user, $obra) === RolObra::Administrador->value;
    }

    /**
     * ¿El usuario es Administrador en al menos una obra? Determina si ve
     * funciones transversales de administradora de obra (certificados y la
     * configuración de permisos de sus obras) sin ser Admin de plataforma.
     */
    public static function esAdministradorDeAlgunaObra(User $user): bool
    {
        return $user->obras()
            ->wherePivot('rol_obra', RolObra::Administrador->value)
            ->exists();
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
        // El Gerente cuenta como miembro a efectos de lectura (ver la obra, su
        // equipo, etc.) sin figurar en el pivot.
        return $user->hasAnyRole(RolGlobal::rolesVisionGlobal())
            || self::rolEnObra($user, $obra) !== null;
    }

    /**
     * Estado completo para la UI: rol => [permiso => bool] sobre todo el
     * catálogo. Con `$obra`, refleja la matriz efectiva de esa obra.
     *
     * @return array<string, array<string, bool>>
     */
    public static function estadoCompleto(?Obra $obra = null): array
    {
        $matriz = $obra !== null ? self::matrizPara($obra) : self::matriz();
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
     * Reescribe la matriz global o la de una obra. $datos: rol => [permiso => bool].
     *
     * @param  array<string, array<string, bool>>  $datos
     */
    public static function sincronizar(array $datos, ?Obra $obra = null): void
    {
        $roles = RolObra::values();
        $permisos = self::permisosValidos();
        $obraId = $obra?->id;
        $filas = [];

        foreach ($datos as $rol => $capacidades) {
            if (! in_array($rol, $roles, true)) {
                continue;
            }

            foreach ($capacidades as $permiso => $activo) {
                if (
                    $activo
                    && in_array($permiso, $permisos, true)
                    && self::permitidoParaRol($permiso, $rol)
                ) {
                    $filas[] = ['obra_id' => $obraId, 'rol_obra' => $rol, 'permiso' => $permiso];
                }
            }
        }

        DB::transaction(function () use ($filas, $obraId) {
            DB::table('permisos_obra')
                ->when($obraId === null,
                    fn ($q) => $q->whereNull('obra_id'),
                    fn ($q) => $q->where('obra_id', $obraId))
                ->delete();

            if ($filas !== []) {
                DB::table('permisos_obra')->insert($filas);
            }
        });

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Sincroniza la matriz de UNA obra manteniendo intacto el rol
     * Administrador: la administradora configura a su equipo (Residente,
     * Supervisor, Especialista, Asistente), no sus propios poderes. Además,
     * `sincronizar()` ya impide conceder la caja chica a otros roles.
     *
     * @param  array<string, array<string, bool>>  $datos
     */
    public static function sincronizarObra(array $datos, Obra $obra): void
    {
        $adminDefault = self::matriz()[RolObra::Administrador->value] ?? [];
        $datos[RolObra::Administrador->value] = [];
        foreach (self::permisosValidos() as $permiso) {
            $datos[RolObra::Administrador->value][$permiso] = in_array($permiso, $adminDefault, true);
        }

        self::sincronizar($datos, $obra);
    }

    /**
     * Elimina la matriz propia de la obra: vuelve a regirse por la global.
     */
    public static function restaurarDefecto(Obra $obra): void
    {
        DB::table('permisos_obra')->where('obra_id', $obra->id)->delete();

        Cache::forget(self::CACHE_KEY);
    }
}
