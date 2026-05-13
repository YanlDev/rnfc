<?php

namespace App\Services;

use App\Models\Carpeta;
use App\Models\Obra;
use Illuminate\Support\Facades\DB;

class PlantillaCarpetasService
{
    /**
     * Devuelve la plantilla cargada del config, normalizada al formato
     * canónico con keys `nombre`, `descripcion` y `subcarpetas` siempre
     * array de strings.
     *
     * @return array<int, array{nombre: string, descripcion: ?string, subcarpetas: array<int, array{nombre: string, hijos: array<int, string>}>}>
     */
    public function plantilla(): array
    {
        $bruto = config('plantilla_carpetas_obra', []);
        $out = [];
        foreach ($bruto as $grupo) {
            $subs = [];
            foreach ($grupo['subcarpetas'] ?? [] as $sub) {
                if (is_string($sub)) {
                    $subs[] = ['nombre' => $sub, 'hijos' => []];
                } elseif (is_array($sub) && isset($sub['nombre'])) {
                    $hijos = [];
                    foreach ($sub['subcarpetas'] ?? [] as $h) {
                        if (is_string($h)) {
                            $hijos[] = $h;
                        }
                    }
                    $subs[] = ['nombre' => $sub['nombre'], 'hijos' => $hijos];
                }
            }
            $out[] = [
                'nombre' => $grupo['nombre'],
                'descripcion' => $grupo['descripcion'] ?? null,
                'subcarpetas' => $subs,
            ];
        }

        return $out;
    }

    /**
     * Aplica una selección sobre una obra.
     *
     * $seleccion es un array con la forma:
     *   [
     *     '01_GESTION_CONTRACTUAL' => ['Contrato', 'Bases_Integradas', ...],
     *     '02_EXPEDIENTE_TECNICO'  => ['Planos', 'Memoria_Descriptiva', ...],
     *   ]
     *
     * Si una subcarpeta tiene hijos definidos en la plantilla, éstos se
     * crean automáticamente al seleccionarla. La operación es idempotente:
     * carpetas ya existentes con la misma ruta no se duplican.
     *
     * @param  array<string, array<int, string>>  $seleccion
     * @return int número de carpetas creadas
     */
    public function aplicar(Obra $obra, array $seleccion, ?int $usuarioId = null): int
    {
        $plantilla = collect($this->plantilla())->keyBy('nombre');

        return DB::transaction(function () use ($obra, $seleccion, $plantilla, $usuarioId) {
            $creadas = 0;
            $orden = 0;

            foreach ($seleccion as $nombreGrupo => $nombresSubs) {
                $grupo = $plantilla->get($nombreGrupo);
                if (! $grupo) {
                    continue;
                }
                $nombresSubs = collect($nombresSubs)->filter()->unique()->values()->all();
                if (empty($nombresSubs)) {
                    continue;
                }

                // 1. Crear (o reutilizar) la carpeta raíz del grupo.
                $raiz = $this->upsertCarpeta(
                    obra: $obra,
                    parent: null,
                    nombre: $grupo['nombre'],
                    orden: $orden++,
                    usuarioId: $usuarioId,
                    creadas: $creadas,
                );

                // 2. Recorrer las subcarpetas del grupo seleccionadas.
                $subsDisponibles = collect($grupo['subcarpetas'])->keyBy('nombre');
                $subOrden = 0;
                foreach ($nombresSubs as $nombreSub) {
                    $sub = $subsDisponibles->get($nombreSub);
                    if (! $sub) {
                        continue;
                    }
                    $carpetaSub = $this->upsertCarpeta(
                        obra: $obra,
                        parent: $raiz,
                        nombre: $sub['nombre'],
                        orden: $subOrden++,
                        usuarioId: $usuarioId,
                        creadas: $creadas,
                    );

                    // 3. Crear los hijos automáticos (sub-subcarpetas).
                    $hijoOrden = 0;
                    foreach ($sub['hijos'] as $hijo) {
                        $this->upsertCarpeta(
                            obra: $obra,
                            parent: $carpetaSub,
                            nombre: $hijo,
                            orden: $hijoOrden++,
                            usuarioId: $usuarioId,
                            creadas: $creadas,
                        );
                    }
                }
            }

            return $creadas;
        });
    }

    private function upsertCarpeta(
        Obra $obra,
        ?Carpeta $parent,
        string $nombre,
        int $orden,
        ?int $usuarioId,
        int &$creadas,
    ): Carpeta {
        $slug = Carpeta::slugify($nombre);
        $ruta = $parent ? "{$parent->ruta}/{$slug}" : $slug;

        $existente = Carpeta::where('obra_id', $obra->id)
            ->where('ruta', $ruta)
            ->first();
        if ($existente) {
            return $existente;
        }

        $carpeta = Carpeta::create([
            'obra_id' => $obra->id,
            'parent_id' => $parent?->id,
            'nombre' => $nombre,
            'ruta' => $ruta,
            'orden' => $orden,
            'creado_por' => $usuarioId,
        ]);
        $creadas++;

        return $carpeta;
    }
}
