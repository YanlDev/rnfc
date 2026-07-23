<?php

namespace App\Http\Requests;

use App\Enums\CategoriaCaja;
use App\Enums\MetodoIngreso;
use App\Enums\TipoComprobante;
use App\Enums\TipoMovimientoCaja;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCajaMovimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización fina (caja.registrar) la valida el controlador con la
        // policy sobre la obra ya resuelta por route-model-binding.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(TipoMovimientoCaja::values())],
            'monto' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'descripcion' => ['required', 'string', 'max:255'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            // Los gastos se rinden por tipo de comprobante (factura/boleta/recibo).
            'tipo_comprobante' => [
                'nullable',
                Rule::requiredIf(fn () => $this->input('tipo') === TipoMovimientoCaja::Egreso->value),
                Rule::in(TipoComprobante::values()),
            ],
            'numero_comprobante' => ['nullable', 'string', 'max:50'],
            'proveedor' => ['nullable', 'string', 'max:255'],
            // Vía del depósito, sólo para ingresos.
            'metodo' => ['nullable', Rule::in(MetodoIngreso::values())],
            // Categoría opcional (clasificación interna, ya no se exige).
            'categoria' => ['nullable', Rule::in(CategoriaCaja::values())],
            'comprobante' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'], // 10 MB, boleta/factura
        ];
    }

    protected function prepareForValidation(): void
    {
        // En ingresos no aplican los campos de gasto, y viceversa.
        if ($this->input('tipo') === TipoMovimientoCaja::Ingreso->value) {
            $this->merge(['categoria' => null, 'tipo_comprobante' => null, 'proveedor' => null]);
        } elseif ($this->input('tipo') === TipoMovimientoCaja::Egreso->value) {
            $this->merge(['metodo' => null]);
        }
    }
}
