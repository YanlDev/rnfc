<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class NotificacionController extends Controller
{
    public function index(): Response
    {
        $user = request()->user();
        abort_unless($user, 401);

        $notificaciones = $user->notifications()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (DatabaseNotification $n) => $this->serializar($n))
            ->all();

        return Inertia::render('notificaciones/index', [
            'notificaciones' => $notificaciones,
        ]);
    }

    public function marcarLeida(string $id): RedirectResponse
    {
        $user = request()->user();
        abort_unless($user, 401);

        $notif = $user->notifications()->findOrFail($id);
        $notif->markAsRead();

        $destino = $notif->data['url'] ?? null;
        if ($destino) {
            return redirect($destino);
        }

        return back();
    }

    public function marcarTodasLeidas(): RedirectResponse
    {
        $user = request()->user();
        abort_unless($user, 401);

        $user->unreadNotifications->markAsRead();

        return back()->with('success', 'Todas las notificaciones fueron marcadas como leídas.');
    }

    public function eliminar(string $id): RedirectResponse
    {
        $user = request()->user();
        abort_unless($user, 401);

        $user->notifications()->where('id', $id)->delete();

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializar(DatabaseNotification $n): array
    {
        $data = $n->data ?? [];

        return [
            'id' => $n->id,
            'tipo' => $data['tipo'] ?? 'general',
            'titulo' => $data['titulo'] ?? 'Notificación',
            'mensaje' => $data['mensaje'] ?? '',
            'obra_codigo' => $data['obra_codigo'] ?? null,
            'obra_nombre' => $data['obra_nombre'] ?? null,
            'url' => $data['url'] ?? null,
            'icono' => $data['icono'] ?? 'Bell',
            'color' => $data['color'] ?? '#145694',
            'leida' => $n->read_at !== null,
            'created_at' => $n->created_at?->toIso8601String(),
            'created_at_relativo' => $n->created_at?->locale('es')->diffForHumans(),
        ];
    }
}
