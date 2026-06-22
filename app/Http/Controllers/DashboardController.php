<?php

namespace App\Http\Controllers;

use App\Enums\EstadoObra;
use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\AsientoCuaderno;
use App\Models\Documento;
use App\Models\Obra;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $visionGlobal = $user->hasAnyRole(RolGlobal::rolesVisionGlobal());

        // Un usuario normal sólo ve las obras donde participa; el admin, todas.
        $obraIds = $visionGlobal
            ? Obra::pluck('id')
            : $user->obras()->pluck('obras.id');

        $kpis = [
            'totalObras' => $obraIds->count(),
            'enEjecucion' => Obra::whereIn('id', $obraIds)
                ->where('estado', EstadoObra::EnEjecucion->value)->count(),
            'finalizadas' => Obra::whereIn('id', $obraIds)
                ->where('estado', EstadoObra::Finalizada->value)->count(),
            'paralizadas' => Obra::whereIn('id', $obraIds)
                ->where('estado', EstadoObra::Paralizada->value)->count(),
        ];

        return Inertia::render('dashboard', [
            'esAdmin' => $visionGlobal,
            'kpis' => $kpis,
            'misObras' => $this->misObras($user, $visionGlobal),
            'actividadReciente' => $this->actividadReciente($obraIds),
        ]);
    }

    /**
     * Obras a mostrar como acceso rápido. Para el usuario normal incluye su
     * rol dentro de cada obra; para el admin (visión global) no aplica.
     *
     * @return array<int, array<string, mixed>>
     */
    private function misObras(User $user, bool $visionGlobal): array
    {
        // Calificadas con la tabla porque el listado del usuario pasa por el
        // join de obra_user y sin prefijo "id" sería ambiguo.
        $campos = [
            'obras.id',
            'obras.codigo',
            'obras.nombre',
            'obras.ubicacion',
            'obras.estado',
            'obras.updated_at',
        ];

        if ($visionGlobal) {
            return Obra::orderByDesc('updated_at')
                ->limit(6)
                ->get($campos)
                ->map(fn (Obra $o) => $this->serializarObra($o, null))
                ->all();
        }

        return $user->obras()
            ->orderByDesc('obras.updated_at')
            ->limit(6)
            ->get($campos)
            ->map(fn (Obra $o) => $this->serializarObra(
                $o,
                RolObra::tryFrom($o->pivot->rol_obra)?->label(),
            ))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializarObra(Obra $obra, ?string $rolLabel): array
    {
        return [
            'id' => $obra->id,
            'codigo' => $obra->codigo,
            'nombre' => $obra->nombre,
            'ubicacion' => $obra->ubicacion,
            'estado' => $obra->estado->value,
            'estado_label' => $obra->estado->label(),
            'rol' => $rolLabel,
        ];
    }

    /**
     * Feed de actividad reciente acotado a las obras visibles para el usuario.
     *
     * @return array<int, array<string, mixed>>
     */
    private function actividadReciente(Collection $obraIds): array
    {
        if ($obraIds->isEmpty()) {
            return [];
        }

        $eventos = collect();

        AsientoCuaderno::with('obra:id,codigo')
            ->whereIn('obra_id', $obraIds)
            ->latest()
            ->limit(8)
            ->get(['id', 'obra_id', 'numero', 'tipo_autor', 'fecha', 'created_at'])
            ->each(function (AsientoCuaderno $a) use ($eventos) {
                $eventos->push([
                    'icono' => 'NotebookPen',
                    'titulo' => "Asiento N° {$a->numero} · ".$a->tipo_autor->labelCorto(),
                    'subtitulo' => 'Obra '.($a->obra?->codigo ?? '—'),
                    'enlace' => route('obras.cuaderno.index', ['obra' => $a->obra_id, 'tipo' => $a->tipo_autor->value]),
                    'created_at' => $a->created_at,
                ]);
            });

        Documento::vigentes()
            ->with('obra:id,codigo')
            ->whereIn('obra_id', $obraIds)
            ->latest()
            ->limit(8)
            ->get(['id', 'obra_id', 'nombre_original', 'version', 'created_at', 'updated_at'])
            ->each(function (Documento $d) use ($eventos) {
                $eventos->push([
                    'icono' => 'FileText',
                    'titulo' => $d->nombre_original.($d->version > 1 ? " (v{$d->version})" : ''),
                    'subtitulo' => 'Obra '.($d->obra?->codigo ?? '—'),
                    'enlace' => route('obras.documentos.index', $d->obra_id),
                    'created_at' => $d->updated_at,
                ]);
            });

        return $eventos
            ->sortByDesc('created_at')
            ->take(8)
            ->map(fn ($e) => array_merge($e, [
                'created_at_relativo' => Carbon::parse($e['created_at'])->locale('es')->diffForHumans(),
            ]))
            ->values()
            ->all();
    }
}
