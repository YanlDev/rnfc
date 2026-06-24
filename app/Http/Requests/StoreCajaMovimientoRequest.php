<?php

namespace App\Http\Requests;

use App\Enums\CategoriaCaja;
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
            // La categoría sólo aplica (y se exige) a los egresos.
            'categoria' => [
                'nullable',
                Rule::requiredIf(fn () => $this->input('tipo') === TipoMovimientoCaja::Egreso->value),
                Rule::in(CategoriaCaja::values()),
            ],
            'comprobante' => ['nullable', 'file', 'max:51200'], // 50 MB
        ];
    }

    protected function prepareForValidation(): void
    {
        // En ingresos no guardamos categoría.
        if ($this->input('tipo') === TipoMovimientoCaja::Ingreso->value) {
            $this->merge(['categoria' => null]);
        }
    }
}
