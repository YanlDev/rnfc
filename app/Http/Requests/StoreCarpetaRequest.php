<?php

namespace App\Http\Requests;

use App\Models\Carpeta;
use App\Models\Obra;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCarpetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $obra = $this->route('obra');

        return $obra instanceof Obra
            && ($this->user()?->can('create', [Carpeta::class, $obra]) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $obra = $this->route('obra');

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('carpetas', 'id')->where('obra_id', $obra?->id),
            ],
        ];
    }
}
