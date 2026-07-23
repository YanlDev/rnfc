<?php

namespace App\Http\Controllers;

use App\Enums\FormaPagoAlquiler;
use App\Enums\TipoComprobante;
use App\Enums\TipoMovimientoCaja;
use App\Models\Alquiler;
use App\Models\AlquilerPago;
use App\Models\CajaMovimiento;
use App\Models\Obra;
use App\Support\PermisosObra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Alquileres recurrentes de una obra (oficina, habitaciones…). Viven dentro
 * del módulo de caja chica y reutilizan sus permisos: al pagar un mes se
 * genera automáticamente el egreso (recibo) en la caja.
 */
class AlquilerController extends Controller
{
    public function store(Request $request, Obra $obra): RedirectResponse
    {
        $this->autorizar($request, $obra, 'caja.registrar');

        $data = $request->validate([
            'inquilino' => ['required', 'string', 'max:255'],
            'arrendador' => ['nullable', 'string', 'max:255'],
            'monto_mensual' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'forma_pago' => ['required', Rule::in(FormaPagoAlquiler::values())],
            'fecha_inicio' => ['required', 'date'],
        ]);

        $obra->alquileres()->create($data);

        return back()->with('success', 'Alquiler agregado.');
    }

    public function update(Request $request, Obra $obra, Alquiler $alquiler): RedirectResponse
    {
        abort_unless($alquiler->obra_id === $obra->id, 404);
        $this->autorizar($request, $obra, 'caja.registrar');

        $data = $request->validate([
            'inquilino' => ['sometimes', 'required', 'string', 'max:255'],
            'arrendador' => ['sometimes', 'nullable', 'string', 'max:255'],
            'monto_mensual' => ['sometimes', 'required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'forma_pago' => ['sometimes', 'required', Rule::in(FormaPagoAlquiler::values())],
            'fecha_inicio' => ['sometimes', 'required', 'date'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $alquiler->update($data);

        return back();
    }

    public function destroy(Request $request, Obra $obra, Alquiler $alquiler): RedirectResponse
    {
        abort_unless($alquiler->obra_id === $obra->id, 404);
        $this->autorizar($request, $obra, 'caja.gestionar');

        // Los pagos ya rendidos (y sus egresos en caja) se conservan.
        $alquiler->delete();

        return back()->with('success', 'Alquiler eliminado.');
    }

    /**
     * Marcar un mes como pagado: crea el pago y su egreso (recibo) en caja.
     */
    public function pagar(Request $request, Obra $obra, Alquiler $alquiler): RedirectResponse
    {
        abort_unless($alquiler->obra_id === $obra->id, 404);
        $this->autorizar($request, $obra, 'caja.registrar');

        $data = $request->validate([
            'periodo' => ['required', 'date_format:Y-m'],
            'fecha_pago' => ['required', 'date', 'before_or_equal:today'],
            'monto' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
        ]);

        $periodo = Carbon::createFromFormat('Y-m', $data['periodo'])->startOfMonth();

        abort_if(
            $alquiler->pagos()->whereDate('periodo', $periodo)->exists(),
            422,
            'Ese mes ya está pagado.',
        );

        $mes = mb_strtoupper($periodo->locale('es')->translatedFormat('F Y'));

        DB::transaction(function () use ($request, $obra, $alquiler, $data, $periodo, $mes) {
            $movimiento = $obra->cajaMovimientos()->create([
                'tipo' => TipoMovimientoCaja::Egreso,
                'tipo_comprobante' => TipoComprobante::Recibo,
                // El pago se deposita al arrendador (dueño); si no se registró,
                // usamos el inquilino como referencia.
                'proveedor' => $alquiler->arrendador ?: $alquiler->inquilino,
                'monto' => $data['monto'],
                'descripcion' => "PAGO ALQUILER {$alquiler->inquilino} {$mes}",
                'fecha' => $data['fecha_pago'],
                'registrado_por' => $request->user()?->id,
            ]);

            $alquiler->pagos()->create([
                'periodo' => $periodo,
                'fecha_pago' => $data['fecha_pago'],
                'monto' => $data['monto'],
                'caja_movimiento_id' => $movimiento->id,
            ]);
        });

        return back()->with('success', 'Pago registrado.');
    }

    /**
     * Deshacer un pago (clic por error): elimina el pago y su egreso de caja.
     */
    public function anularPago(Request $request, Obra $obra, Alquiler $alquiler, AlquilerPago $pago): RedirectResponse
    {
        abort_unless($alquiler->obra_id === $obra->id && $pago->alquiler_id === $alquiler->id, 404);
        $this->autorizar($request, $obra, 'caja.registrar');

        DB::transaction(function () use ($pago) {
            $movimiento = $pago->cajaMovimiento;
            $pago->delete();
            $movimiento?->delete(); // soft delete del egreso vinculado
        });

        return back()->with('success', 'Pago anulado.');
    }

    private function autorizar(Request $request, Obra $obra, string $permiso): void
    {
        $user = $request->user();

        abort_unless($user !== null && PermisosObra::puede($user, $obra, $permiso), 403);
    }
}
