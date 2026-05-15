<?php

namespace App\Http\Controllers;

use App\Enums\RolGlobal;
use App\Enums\TipoAutorCuaderno;
use App\Http\Requests\StoreAsientoCuadernoRequest;
use App\Models\AsientoCuaderno;
use App\Models\Obra;
use App\Notifications\AsientoCuadernoCreado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AsientoCuadernoController extends Controller
{
    private const DISCO = 'documentos';

    public function index(Obra $obra): Response
    {
        $this->authorize('viewAny', [AsientoCuaderno::class, $obra]);

        $tipoParam = request()->string('tipo', 'supervisor')->toString();
        $tipo = TipoAutorCuaderno::tryFrom($tipoParam) ?? TipoAutorCuaderno::Supervisor;

        $asientos = AsientoCuaderno::query()
            ->where('obra_id', $obra->id)
            ->where('tipo_autor', $tipo->value)
            ->with('autor:id,name')
            ->orderByDesc('fecha')
            ->orderByDesc('numero')
            ->get()
            ->map(fn (AsientoCuaderno $a) => $this->serializar($a))
            ->all();

        $resumenPorTipo = collect(TipoAutorCuaderno::cases())->map(function (TipoAutorCuaderno $t) use ($obra) {
            $total = AsientoCuaderno::where('obra_id', $obra->id)
                ->where('tipo_autor', $t->value)
                ->count();

            return [
                'value' => $t->value,
                'label' => $t->label(),
                'label_corto' => $t->labelCorto(),
                'total' => $total,
            ];
        })->all();

        return Inertia::render('obras/cuaderno/index', [
            'obra' => [
                'id' => $obra->id,
                'codigo' => $obra->codigo,
                'nombre' => $obra->nombre,
            ],
            'tipoActivo' => $tipo->value,
            'cuadernos' => $resumenPorTipo,
            'asientos' => $asientos,
            'siguienteNumero' => AsientoCuaderno::siguienteNumero($obra->id, $tipo),
            'puedeEscribir' => request()->user()?->can('createEn', [AsientoCuaderno::class, $obra, $tipo]) ?? false,
            'puedeEliminar' => request()->user()?->hasAnyRole(RolGlobal::rolesAdministrativos()) ?? false,
        ]);
    }

    public function store(StoreAsientoCuadernoRequest $request, Obra $obra): RedirectResponse
    {
        $data = $request->validated();
        $tipo = TipoAutorCuaderno::from($data['tipo_autor']);

        $asiento = DB::transaction(function () use ($obra, $tipo, $data, $request) {
            $asiento = new AsientoCuaderno([
                'obra_id' => $obra->id,
                'tipo_autor' => $tipo->value,
                'fecha' => $data['fecha'],
                'contenido' => $data['contenido'],
                'autor_id' => $request->user()?->id,
            ]);
            $asiento->numero = AsientoCuaderno::siguienteNumero($obra->id, $tipo);

            if ($archivo = $request->file('archivo')) {
                $ext = strtolower($archivo->getClientOriginalExtension()) ?: 'bin';
                $nombre = Str::ulid().'.'.$ext;
                $directorio = "obras/{$obra->id}/_cuaderno/{$tipo->value}";
                $ruta = Storage::disk(self::DISCO)->putFileAs($directorio, $archivo, $nombre);

                $asiento->archivo_path = $ruta;
                $asiento->archivo_nombre_original = $archivo->getClientOriginalName();
                $asiento->archivo_mime = $archivo->getMimeType() ?? 'application/octet-stream';
                $asiento->archivo_tamano = $archivo->getSize() ?? 0;
            }

            $asiento->save();

            return $asiento;
        });

        // Notificar a miembros de la obra (excepto al autor).
        $miembros = $obra->usuarios()
            ->where('users.id', '!=', $request->user()?->id ?? 0)
            ->get();
        if ($miembros->isNotEmpty()) {
            Notification::send($miembros, new AsientoCuadernoCreado($asiento));
        }

        return redirect()
            ->route('obras.cuaderno.index', ['obra' => $obra->id, 'tipo' => $tipo->value])
            ->with('success', 'Asiento registrado.');
    }

    public function destroy(Obra $obra, AsientoCuaderno $asiento): RedirectResponse
    {
        abort_unless($asiento->obra_id === $obra->id, 404);
        $this->authorize('delete', $asiento);

        $asiento->delete(); // soft delete (mantenemos archivo físico por trazabilidad)

        return back()->with('success', 'Asiento eliminado.');
    }

    public function descargar(Obra $obra, AsientoCuaderno $asiento): StreamedResponse
    {
        abort_unless($asiento->obra_id === $obra->id, 404);
        $this->authorize('view', $asiento);
        abort_unless($asiento->tieneArchivo(), 404);

        return Storage::disk(self::DISCO)->download(
            $asiento->archivo_path,
            $asiento->archivo_nombre_original ?? 'asiento.pdf',
        );
    }

    public function preview(Obra $obra, AsientoCuaderno $asiento): StreamedResponse
    {
        abort_unless($asiento->obra_id === $obra->id, 404);
        $this->authorize('view', $asiento);
        abort_unless($asiento->tieneArchivo(), 404);

        return Storage::disk(self::DISCO)->response(
            $asiento->archivo_path,
            $asiento->archivo_nombre_original ?? 'asiento.pdf',
            ['Content-Type' => $asiento->archivo_mime ?? 'application/pdf'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function serializar(AsientoCuaderno $a): array
    {
        return [
            'id' => $a->id,
            'numero' => $a->numero,
            'fecha' => $a->fecha?->format('Y-m-d'),
            'contenido' => $a->contenido,
            'autor' => $a->autor?->name,
            'tiene_archivo' => $a->tieneArchivo(),
            'es_pdf' => $a->esPdf(),
            'archivo_nombre' => $a->archivo_nombre_original,
            'archivo_mime' => $a->archivo_mime,
            'archivo_tamano_humano' => $a->tamanoFormateado(),
            'url_preview' => $a->tieneArchivo()
                ? route('obras.cuaderno.preview', [$a->obra_id, $a])
                : null,
            'url_descarga' => $a->tieneArchivo()
                ? route('obras.cuaderno.descargar', [$a->obra_id, $a])
                : null,
            'created_at' => $a->created_at?->toIso8601String(),
        ];
    }
}
