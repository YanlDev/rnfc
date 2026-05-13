<?php

namespace App\Http\Requests;

use App\Models\Carpeta;
use App\Models\Obra;
use Illuminate\Foundation\Http\FormRequest;

class AplicarPlantillaRequest extends FormRequest
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
        return [
            'seleccion' => ['required', 'array'],
            'seleccion.*' => ['array'],
            'seleccion.*.*' => ['string'],
        ];
    }
}
