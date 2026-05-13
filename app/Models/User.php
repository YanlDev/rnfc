<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'desactivado_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function obras(): BelongsToMany
    {
        return $this->belongsToMany(Obra::class, 'obra_user')
            ->withPivot(['rol_obra', 'asignado_at'])
            ->withTimestamps();
    }

    /* =========================================================
     | Estado de la cuenta
     |========================================================= */

    public function estaActivo(): bool
    {
        return $this->desactivado_at === null;
    }

    public function scopeActivos($query)
    {
        return $query->whereNull('desactivado_at');
    }

    public function scopeDesactivados($query)
    {
        return $query->whereNotNull('desactivado_at');
    }

    public function desactivadoPor()
    {
        return $this->belongsTo(self::class, 'desactivado_por');
    }
}
