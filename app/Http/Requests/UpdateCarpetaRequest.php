<?php

namespace App\Http\Requests;

use App\Models\Carpeta;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCarpetaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $carpeta = $this->route('carpeta');

        return $carpeta instanceof Carpeta
            && ($this->user()?->can('update', $carpeta) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
        ];
    }
}
