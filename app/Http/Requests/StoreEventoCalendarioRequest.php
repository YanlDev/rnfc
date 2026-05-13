<?php

namespace App\Http\Requests;

use App\Enums\TipoEvento;
use App\Models\EventoCalendario;
use App\Models\Obra;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventoCalendarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        $obra = $this->route('obra');
        $evento = $this->route('evento');

        if ($evento instanceof EventoCalendario) {
            return $this->user()?->can('update', $evento) ?? false;
        }

        return $obra instanceof Obra
            && ($this->user()?->can('create', [EventoCalendario::class, $obra]) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::enum(TipoEvento::class)],
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'todo_el_dia' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tipo.required' => 'Selecciona el tipo de evento.',
            'titulo.required' => 'El título es obligatorio.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la de inicio.',
        ];
    }
}
