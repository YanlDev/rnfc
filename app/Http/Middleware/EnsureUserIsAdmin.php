<?php

namespace App\Http\Middleware;

use App\Enums\RolGlobal;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe el acceso a funciones de plataforma (configuración de marca,
 * galería pública, etc.) a usuarios con rol global administrativo.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user && $user->hasAnyRole(RolGlobal::rolesAdministrativos()),
            403,
        );

        return $next($request);
    }
}
