<?php

namespace App\Http\Controllers;

use App\Enums\CategoriaCaja;
use App\Enums\TipoMovimientoCaja;
use App\Http\Requests\StoreCajaMovimientoRequest;
use App\Models\CajaMovimiento;
use App\Models\Obra;
use App\Support\PermisosObra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CajaController extends Controller
{
    private const DISCO = 'documentos';

    public function index(Obra $obra): Response
    {
        $this->authorize('viewAny', [CajaMovimiento::class, $obra]);

        $movimientos = $obra->cajaMovimientos()
            ->with('registradoPor:id,name')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        $ingresos = (float) $movimientos->where('tipo', TipoMovimientoCaja::Ingreso)->sum('monto');
        $egresos = (float) $movimientos->where('tipo', TipoMovimientoCaja::Egreso)->sum('monto');

        return Inertia::render('obras/caja/index', [
            'obra' => [
                'id' => $obra->id,
                'codigo' => $obra->codigo,
                'nombre' => $obra->nombre,
            ],
            'movimientos' => $movimientos->map(fn (CajaMovimiento $m) => $this->serializar($m))->all(),
            'resumen' => [
                'ingresos' => round($ingresos, 2),
                'egresos' => round($egresos, 2),
                'saldo' => round($ingresos - $egresos, 2),
            ],
            'categorias' => CategoriaCaja::opciones(),
            'puedeRegistrar' => request()->user()?->can('create', [CajaMovimiento::class, $obra]) ?? false,
            'puedeGestionar' => $this->puedeGestionar($obra),
        ]);
    }

    public function store(StoreCajaMovimientoRequest $request, Obra $obra): RedirectResponse
    {
        $this->authorize('create', [CajaMovimiento::class, $obra]);

        $data = $request->validated();

        // Comprobante opcional: lo persistimos en disco antes de la BD.
        $comprobante = [];
        if ($archivo = $request->file('comprobante')) {
            $ext = strtolower($archivo->getClientOriginalExtension()) ?: 'bin';
            $nombre = Str::ulid().'.'.$ext;
            $ruta = Storage::disk(self::DISCO)->putFileAs("obras/{$obra->id}/_caja", $archivo, $nombre);

            abort_if($ruta === false || $ruta === '', 500, 'No se pudo almacenar el comprobante.');

            $comprobante = [
                'comprobante_path' => $ruta,
                'comprobante_nombre_original' => $archivo->getClientOriginalName(),
                'comprobante_mime' => $archivo->getMimeType() ?? 'application/octet-stream',
                'comprobante_tamano' => $archivo->getSize() ?? 0,
            ];
        }

        try {
            $obra->cajaMovimientos()->create([
                'tipo' => $data['tipo'],
                'categoria' => $data['categoria'] ?? null,
                'monto' => $data['monto'],
                'descripcion' => $data['descripcion'],
                'fecha' => $data['fecha'],
                'registrado_por' => $request->user()?->id,
                ...$comprobante,
            ]);
        } catch (\Throwable $e) {
            if ($comprobante !== []) {
                Storage::disk(self::DISCO)->delete($comprobante['comprobante_path']);
            }
            throw $e;
        }

        return back()->with('success', 'Movimiento registrado.');
    }

    public function destroy(Obra $obra, CajaMovimiento $movimiento): RedirectResponse
    {
        abort_unless($movimiento->obra_id === $obra->id, 404);
        $this->authorize('delete', $movimiento);

        // Soft delete: conservamos el comprobante físico por trazabilidad.
        $movimiento->delete();

        return back()->with('success', 'Movimiento eliminado.');
    }

    public function comprobante(Obra $obra, CajaMovimiento $movimiento): StreamedResponse
    {
        abort_unless($movimiento->obra_id === $obra->id, 404);
        $this->authorize('view', $movimiento);
        abort_unless($movimiento->tieneComprobante(), 404);

        $disk = Storage::disk(self::DISCO);
        abort_unless($disk->exists($movimiento->comprobante_path), 404, 'El comprobante ya no está disponible.');

        return $disk->response(
            $movimiento->comprobante_path,
            $movimiento->comprobante_nombre_original ?? 'comprobante',
            ['Content-Type' => $movimiento->comprobante_mime ?? 'application/octet-stream'],
        );
    }

    private function puedeGestionar(Obra $obra): bool
    {
        $user = request()->user();

        return $user !== null && PermisosObra::puede($user, $obra, 'caja.gestionar');
    }

    /**
     * @return array<string, mixed>
     */
    private function serializar(CajaMovimiento $m): array
    {
        return [
            'id' => $m->id,
            'tipo' => $m->tipo->value,
            'categoria' => $m->categoria?->value,
            'categoria_label' => $m->categoria?->label(),
            'monto' => (float) $m->monto,
            'descripcion' => $m->descripcion,
            'fecha' => $m->fecha?->format('Y-m-d'),
            'registrado_por' => $m->registradoPor?->name,
            'tiene_comprobante' => $m->tieneComprobante(),
            'comprobante_mime' => $m->comprobante_mime,
            'url_comprobante' => $m->tieneComprobante()
                ? route('obras.caja.comprobante', [$m->obra_id, $m])
                : null,
            'created_at' => $m->created_at?->toIso8601String(),
        ];
    }
}
