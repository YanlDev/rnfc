<?php

namespace App\Http\Controllers;

use App\Enums\FormaPagoAlquiler;
use App\Enums\MetodoIngreso;
use App\Enums\TipoComprobante;
use App\Enums\TipoMovimientoCaja;
use App\Http\Requests\StoreCajaMovimientoRequest;
use App\Http\Requests\UpdateCajaMovimientoRequest;
use App\Models\Alquiler;
use App\Models\CajaMovimiento;
use App\Models\Obra;
use App\Support\PermisosObra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $egresosCol = $movimientos->where('tipo', TipoMovimientoCaja::Egreso);
        $egresos = (float) $egresosCol->sum('monto');

        // Subtotales de la rendición, como en el formato de obra.
        $porComprobante = [];
        foreach (TipoComprobante::cases() as $tc) {
            $porComprobante[$tc->value] = round(
                (float) $egresosCol->where('tipo_comprobante', $tc)->sum('monto'),
                2,
            );
        }
        $porComprobante['sin_tipo'] = round(
            (float) $egresosCol->whereNull('tipo_comprobante')->sum('monto'),
            2,
        );

        $alquileres = $obra->alquileres()
            ->with('pagos')
            ->orderByDesc('activo')
            ->orderBy('inquilino')
            ->get();

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
                'por_comprobante' => $porComprobante,
            ],
            'alquileres' => $alquileres->map(fn (Alquiler $a) => $this->serializarAlquiler($a))->all(),
            // Autocompletado de proveedores ya usados en esta obra.
            'proveedores' => $egresosCol->pluck('proveedor')->filter()->unique()->sort()->values()->all(),
            'tiposComprobante' => TipoComprobante::opciones(),
            'metodos' => MetodoIngreso::opciones(),
            'formasPago' => FormaPagoAlquiler::opciones(),
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
            $comprobante = $this->guardarComprobante($obra, $archivo);
        }

        try {
            $obra->cajaMovimientos()->create([
                'tipo' => $data['tipo'],
                'categoria' => $data['categoria'] ?? null,
                'tipo_comprobante' => $data['tipo_comprobante'] ?? null,
                'numero_comprobante' => $data['numero_comprobante'] ?? null,
                'proveedor' => $data['proveedor'] ?? null,
                'metodo' => $data['metodo'] ?? null,
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

    /**
     * Edición inline de una celda/fila de la tabla.
     */
    public function update(UpdateCajaMovimientoRequest $request, Obra $obra, CajaMovimiento $movimiento): RedirectResponse
    {
        abort_unless($movimiento->obra_id === $obra->id, 404);
        $this->authorize('update', $movimiento);

        $movimiento->update($request->validated());

        return back();
    }

    /**
     * Adjuntar (o reemplazar) el comprobante de un movimiento existente.
     */
    public function subirComprobante(Request $request, Obra $obra, CajaMovimiento $movimiento): RedirectResponse
    {
        abort_unless($movimiento->obra_id === $obra->id, 404);
        $this->authorize('update', $movimiento);

        $request->validate([
            'comprobante' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
        ]);

        $anterior = $movimiento->comprobante_path;
        $datos = $this->guardarComprobante($obra, $request->file('comprobante'));

        $movimiento->update($datos);

        // El archivo anterior deja de referenciarse: lo limpiamos.
        if ($anterior && $anterior !== $datos['comprobante_path']) {
            Storage::disk(self::DISCO)->delete($anterior);
        }

        return back()->with('success', 'Comprobante adjuntado.');
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

        return $this->servirArchivoSeguro(
            $disk,
            $movimiento->comprobante_path,
            $movimiento->comprobante_nombre_original ?? 'comprobante',
            $movimiento->comprobante_mime,
        );
    }

    /**
     * @return array{comprobante_path: string, comprobante_nombre_original: string, comprobante_mime: string, comprobante_tamano: int}
     */
    private function guardarComprobante(Obra $obra, \Illuminate\Http\UploadedFile $archivo): array
    {
        $ext = strtolower($archivo->getClientOriginalExtension()) ?: 'bin';
        $nombre = Str::ulid().'.'.$ext;
        $ruta = Storage::disk(self::DISCO)->putFileAs("obras/{$obra->id}/_caja", $archivo, $nombre);

        abort_if($ruta === false || $ruta === '', 500, 'No se pudo almacenar el comprobante.');

        return [
            'comprobante_path' => $ruta,
            'comprobante_nombre_original' => $archivo->getClientOriginalName(),
            'comprobante_mime' => $archivo->getMimeType() ?? 'application/octet-stream',
            'comprobante_tamano' => $archivo->getSize() ?? 0,
        ];
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
            'tipo_comprobante' => $m->tipo_comprobante?->value,
            'numero_comprobante' => $m->numero_comprobante,
            'proveedor' => $m->proveedor,
            'metodo' => $m->metodo?->value,
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

    /**
     * @return array<string, mixed>
     */
    private function serializarAlquiler(Alquiler $a): array
    {
        return [
            'id' => $a->id,
            'inquilino' => $a->inquilino,
            'arrendador' => $a->arrendador,
            'monto_mensual' => (float) $a->monto_mensual,
            'forma_pago' => $a->forma_pago->value,
            'forma_pago_label' => $a->forma_pago->label(),
            'fecha_inicio' => $a->fecha_inicio->format('Y-m-d'),
            'activo' => $a->activo,
            'pagos' => $a->pagos->map(fn ($p) => [
                'id' => $p->id,
                'periodo' => $p->periodo->format('Y-m'),
                'fecha_pago' => $p->fecha_pago->format('Y-m-d'),
                'monto' => (float) $p->monto,
            ])->all(),
        ];
    }
}
