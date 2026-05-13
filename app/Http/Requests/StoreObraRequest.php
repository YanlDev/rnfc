<?php

namespace App\Http\Requests;

use App\Enums\EstadoObra;
use App\Models\Obra;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreObraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Obra::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:50', 'unique:obras,codigo'],
            'nombre' => ['required', 'string', 'max:5000'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'entidad_contratante' => ['nullable', 'string', 'max:255'],
            'monto_contractual' => ['nullable', 'numeric', 'min:0', 'max:99999999999.99'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin_prevista' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'fecha_fin_real' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', Rule::enum(EstadoObra::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'codigo.required' => 'El código de la obra es obligatorio.',
            'codigo.unique' => 'Ya existe una obra con este código.',
            'nombre.required' => 'El nombre de la obra es obligatorio.',
            'estado.required' => 'El estado de la obra es obligatorio.',
            'fecha_fin_prevista.after_or_equal' => 'La fecha de fin prevista debe ser posterior al inicio.',
            'fecha_fin_real.after_or_equal' => 'La fecha de fin real debe ser posterior al inicio.',
        ];
    }
}
