<?php

namespace App\Http\Middleware;

use App\Enums\RolGlobal;
use App\Services\BrandingService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * El landing público usa su propio root template con SEO renderizado en
     * servidor (meta tags + JSON-LD), ya que la app no tiene SSR de Inertia.
     */
    public function rootView(Request $request): string
    {
        return $request->routeIs('home') ? 'landing-app' : $this->rootView;
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $userArray = null;

        if ($user) {
            // Whitelist explícita: sólo exponemos al frontend los campos que la
            // UI necesita, en vez de volcar toda la fila con $user->toArray().
            $userArray = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'two_factor_confirmed_at' => $user->two_factor_confirmed_at?->toIso8601String(),
                'rol_global' => $user->roles->first()?->name,
                'es_admin' => $user->hasAnyRole(RolGlobal::rolesAdministrativos()),
            ];
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $userArray,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'branding' => fn () => app(BrandingService::class)->urls(),
            'notificacionesHeader' => fn () => $this->notificaciones($request),
        ];
    }

    /**
     * Notificaciones del usuario autenticado: las 8 más recientes + unread count.
     *
     * @return array<string, mixed>
     */
    private function notificaciones(Request $request): array
    {
        $user = $request->user();
        if (! $user) {
            return ['unreadCount' => 0, 'recientes' => []];
        }

        $recientes = $user->notifications()
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(function ($n) {
                $data = $n->data ?? [];

                return [
                    'id' => $n->id,
                    'titulo' => $data['titulo'] ?? 'Notificación',
                    'mensaje' => $data['mensaje'] ?? '',
                    'obra_codigo' => $data['obra_codigo'] ?? null,
                    'url' => $data['url'] ?? null,
                    'icono' => $data['icono'] ?? 'Bell',
                    'color' => $data['color'] ?? '#145694',
                    'leida' => $n->read_at !== null,
                    'created_at_relativo' => $n->created_at?->locale('es')->diffForHumans(),
                ];
            })
            ->all();

        return [
            'unreadCount' => $user->unreadNotifications()->count(),
            'recientes' => $recientes,
        ];
    }
}
