<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GaleriaHome extends Model
{
    protected $table = 'galeria_home';

    protected $fillable = ['ruta', 'titulo', 'orden'];

    protected $appends = ['url'];

    public function getUrlAttribute(): ?string
    {
        if (! $this->ruta) {
            return null;
        }

        return Storage::disk('public')->url($this->ruta);
    }
}
