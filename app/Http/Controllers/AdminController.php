<?php

namespace App\Http\Controllers;

use App\Enums\EstadoObra;
use App\Enums\RolGlobal;
use App\Enums\TipoCertificado;
use App\Models\AsientoCuaderno;
use App\Models\Carpeta;
use App\Models\Certificado;
use App\Models\Documento;
use App\Models\EventoCalendario;
use App\Models\Invitacion;
use App\Models\Obra;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Panel de administración con KPIs cruzados, ranking de almacenamiento
     * y actividad reciente unificada. Sólo accesible para roles administrativos.
     */
    public function __invoke(): Response
    {
        abort_unless(
            request()->user()?->hasAnyRole(RolGlobal::rolesAdministrativos()),
            403,
        );

        return Inertia::render('admin/index', [
            'kpis' => $this->kpis(),
            'estadosObras' => $this->estadosObras(),
            'certificadosPorTipo' => $this->certificadosPorTipo(),
            'almacenamiento' => $this->rankingAlmacenamiento(),
            'documentosPorObra' => $this->topObrasDocumentos(),
            'actividadReciente' => $this->actividadReciente(),
            'usuariosActivos' => $this->topUsuariosActivos(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function kpis(): array
    {
        return [
            'obras_total' => Obra::count(),
            'obras_en_ejecucion' => Obra::where('estado', EstadoObra::EnEjecucion->value)->count(),
            'obras_finalizadas' => Obra::where('estado', EstadoObra::Finalizada->value)->count(),
            'obras_paralizadas' => Obra::where('estado', EstadoObra::Paralizada->value)->count(),
            'certificados_total' => Certificado::count(),
            'certificados_revocados' => Certificado::whereNotNull('revocado_at')->count(),
            'documentos_total' => Documento::vigentes()->count(),
            'documentos_con_versiones' => Documento::where('version', '>', 1)->count(),
            'carpetas_total' => Carpeta::count(),
            'asientos_total' => AsientoCuaderno::count(),
            'eventos_total' => EventoCalendario::count(),
            'usuarios_total' => User::count(),
            'usuarios_activos' => User::whereHas('obras')->count(),
            'invitaciones_pendientes' => Invitacion::whereNull('aceptada_at')
                ->whereNull('cancelada_at')
                ->where('expira_at', '>', now())
                ->count(),
            'almacenamiento_total_bytes' => (int) Documento::sum('tamano'),
        ];
    }

    /**
     * Conteo de obras por estado para el gráfico de distribución.
     *
     * @return array<int, array{value: string, label: string, total: int}>
     */
    private function estadosObras(): array
    {
        $conteos = Obra::query()
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->all();

        return collect(EstadoObra::cases())->map(fn (EstadoObra $e) => [
            'value' => $e->value,
            'label' => $e->label(),
            'total' => (int) ($conteos[$e->value] ?? 0),
        ])->all();
    }

    /**
     * @return array<int, array{value: string, label: string, total: int}>
     */
    private function certificadosPorTipo(): array
    {
        $conteos = Certificado::query()
            ->selectRaw('tipo, count(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->all();

        return collect(TipoCertificado::cases())->map(fn (TipoCertificado $t) => [
            'value' => $t->value,
            'label' => $t->label(),
            'total' => (int) ($conteos[$t->value] ?? 0),
        ])->filter(fn ($r) => $r['total'] > 0)->values()->all();
    }

    /**
     * Ranking de las top 5 obras por almacenamiento.
     *
     * @return array<int, array<string, mixed>>
     */
    private function rankingAlmacenamiento(): array
    {
        return Documento::query()
            ->selectRaw('obra_id, sum(tamano) as bytes, count(*) as documentos')
            ->groupBy('obra_id')
            ->orderByDesc('bytes')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $obra = Obra::find($row->obra_id);
                if (! $obra) {
                    return null;
                }

                return [
                    'obra_id' => $obra->id,
                    'codigo' => $obra->codigo,
                    'nombre' => $obra->nombre,
                    'bytes' => (int) $row->bytes,
                    'tamano_humano' => $this->bytesHumano((int) $row->bytes),
                    'documentos' => (int) $row->documentos,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function topObrasDocumentos(): array
    {
        return Obra::query()
            ->withCount(['carpetas'])
            ->orderByDesc('carpetas_count')
            ->limit(5)
            ->get(['id', 'codigo', 'nombre'])
            ->map(fn (Obra $o) => [
                'obra_id' => $o->id,
                'codigo' => $o->codigo,
                'nombre' => $o->nombre,
                'carpetas' => (int) $o->carpetas_count,
            ])
            ->all();
    }

    /**
     * Top usuarios por número de obras donde participan + asientos creados.
     *
     * @return array<int, array<string, mixed>>
     */
    private function topUsuariosActivos(): array
    {
        return User::query()
            ->withCount(['obras'])
            ->whereHas('obras')
            ->orderByDesc('obras_count')
            ->limit(5)
            ->get(['id', 'name', 'email'])
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'obras' => (int) $u->obras_count,
            ])
            ->all();
    }

    /**
     * Feed unificado de eventos recientes cruzando módulos.
     *
     * @return array<int, array<string, mixed>>
     */
    private function actividadReciente(): array
    {
        $eventos = collect();

        // Certificados emitidos
        Certificado::with('obra:id,codigo')->latest()->limit(10)
            ->get(['id', 'codigo', 'tipo', 'beneficiario_nombre', 'obra_id', 'created_at'])
            ->each(function ($c) use ($eventos) {
                $eventos->push([
                    'tipo' => 'certificado',
                    'icono' => 'Award',
                    'color' => '#145694',
                    'titulo' => "Certificado emitido: {$c->codigo}",
                    'subtitulo' => "A nombre de {$c->beneficiario_nombre}",
                    'enlace' => route('certificados.show', $c->id),
                    'created_at' => $c->created_at,
                ]);
            });

        // Documentos subidos (vigentes)
        Documento::vigentes()
            ->with('obra:id,codigo')
            ->latest()
            ->limit(10)
            ->get(['id', 'obra_id', 'nombre_original', 'version', 'created_at', 'updated_at'])
            ->each(function ($d) use ($eventos) {
                $obraCodigo = $d->obra?->codigo ?? '—';
                $eventos->push([
                    'tipo' => 'documento',
                    'icono' => 'FolderTree',
                    'color' => '#2850da',
                    'titulo' => "Documento: {$d->nombre_original}".($d->version > 1 ? " (v{$d->version})" : ''),
                    'subtitulo' => "Obra {$obraCodigo}",
                    'enlace' => route('obras.documentos.index', $d->obra_id),
                    'created_at' => $d->updated_at,
                ]);
            });

        // Asientos de cuaderno
        AsientoCuaderno::with('obra:id,codigo')->latest()->limit(10)
            ->get(['id', 'obra_id', 'numero', 'tipo_autor', 'fecha', 'created_at'])
            ->each(function ($a) use ($eventos) {
                $obraCodigo = $a->obra?->codigo ?? '—';
                $eventos->push([
                    'tipo' => 'asiento',
                    'icono' => 'NotebookPen',
                    'color' => '#5da235',
                    'titulo' => "Asiento N° {$a->numero} · ".$a->tipo_autor->labelCorto(),
                    'subtitulo' => "Obra {$obraCodigo} · fecha del asiento {$a->fecha->format('d/m/Y')}",
                    'enlace' => route('obras.cuaderno.index', ['obra' => $a->obra_id, 'tipo' => $a->tipo_autor->value]),
                    'created_at' => $a->created_at,
                ]);
            });

        // Obras creadas o actualizadas
        Obra::latest('updated_at')->limit(5)
            ->get(['id', 'codigo', 'nombre', 'created_at', 'updated_at'])
            ->each(function ($o) use ($eventos) {
                $reciente = $o->created_at == $o->updated_at ? 'creada' : 'actualizada';
                $eventos->push([
                    'tipo' => 'obra',
                    'icono' => 'Building2',
                    'color' => '#9ed146',
                    'titulo' => "Obra {$reciente}: {$o->codigo}",
                    'subtitulo' => $o->nombre,
                    'enlace' => route('obras.show', $o->id),
                    'created_at' => $o->updated_at,
                ]);
            });

        return $eventos
            ->sortByDesc('created_at')
            ->take(15)
            ->map(fn ($e) => array_merge($e, [
                'created_at_iso' => Carbon::parse($e['created_at'])->toIso8601String(),
                'created_at_relativo' => Carbon::parse($e['created_at'])->locale('es')->diffForHumans(),
            ]))
            ->values()
            ->all();
    }

    private function bytesHumano(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1024 ** 2) {
            return number_format($bytes / 1024, 1).' KB';
        }
        if ($bytes < 1024 ** 3) {
            return number_format($bytes / (1024 ** 2), 1).' MB';
        }

        return number_format($bytes / (1024 ** 3), 2).' GB';
    }
}
