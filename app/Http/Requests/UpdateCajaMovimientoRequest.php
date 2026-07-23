<?php

namespace App\Http\Requests;

use App\Enums\MetodoIngreso;
use App\Enums\TipoComprobante;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Edición inline (tipo Excel) de un movimiento: sólo llegan los campos tocados.
 * El tipo (ingreso/egreso) no se cambia; para eso se elimina y re-registra.
 */
class UpdateCajaMovimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'monto' => ['sometimes', 'required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'descripcion' => ['sometimes', 'required', 'string', 'max:255'],
            'fecha' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'tipo_comprobante' => ['sometimes', 'nullable', Rule::in(TipoComprobante::values())],
            'numero_comprobante' => ['sometimes', 'nullable', 'string', 'max:50'],
            'proveedor' => ['sometimes', 'nullable', 'string', 'max:255'],
            'metodo' => ['sometimes', 'nullable', Rule::in(MetodoIngreso::values())],
        ];
    }
}
