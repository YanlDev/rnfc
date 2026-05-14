<?php

namespace App\Http\Controllers;

use App\Enums\TipoCertificado;
use App\Http\Requests\StoreCertificadoRequest;
use App\Models\Certificado;
use App\Models\Obra;
use App\Enums\RolGlobal;
use App\Models\User;
use App\Notifications\CertificadoRevocado as CertificadoRevocadoNotif;
use App\Services\BrandingService;
use App\Services\QrService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CertificadoController extends Controller
{
    public function index(): \Inertia\Response
    {
        $this->authorize('viewAny', Certificado::class);

        $certificados = Certificado::with(['obra:id,nombre,codigo', 'emisor:id,name'])
            ->latest('fecha_emision')
            ->paginate(20)
            ->through(fn (Certificado $c) => [
                'id' => $c->id,
                'codigo' => $c->codigo,
                'tipo' => $c->tipo->value,
                'tipo_label' => $c->tipo->label(),
                'beneficiario_nombre' => $c->beneficiario_nombre,
                'beneficiario_documento' => $c->beneficiario_documento,
                'obra' => $c->obra ? ['id' => $c->obra->id, 'nombre' => $c->obra->nombre, 'codigo' => $c->obra->codigo] : null,
                'fecha_emision' => $c->fecha_emision?->format('Y-m-d'),
                'vigente' => $c->estaVigente(),
                'revocado_at' => $c->revocado_at?->toIso8601String(),
            ]);

        return Inertia::render('certificados/index', [
            'certificados' => $certificados,
            'tipos' => collect(TipoCertificado::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ])->all(),
        ]);
    }

    public function create(): \Inertia\Response
    {
        $this->authorize('create', Certificado::class);

        return Inertia::render('certificados/create', [
            'tipos' => collect(TipoCertificado::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => $t->label(),
                'titulo' => $t->titulo(),
                'requiere_obra' => $t->requiereObra(),
            ])->all(),
            'obras' => Obra::orderBy('nombre')->get(['id', 'codigo', 'nombre'])->map(fn ($o) => [
                'id' => $o->id,
                'label' => "[{$o->codigo}] {$o->nombre}",
            ])->all(),
        ]);
    }

    public function store(StoreCertificadoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $anio = (int) date('Y', strtotime($data['fecha_emision']));

        $certificado = new Certificado($data);
        $certificado->codigo = Certificado::generarCodigo($anio);
        $certificado->emitido_por = $request->user()?->id;
        $certificado->hash_verificacion = $certificado->calcularHash();
        $certificado->save();

        return redirect()
            ->route('certificados.show', $certificado)
            ->with('success', "Certificado {$certificado->codigo} emitido correctamente.");
    }

    public function show(Certificado $certificado): \Inertia\Response
    {
        $this->authorize('view', $certificado);

        $certificado->load('obra:id,nombre,codigo,entidad_contratante', 'emisor:id,name');

        return Inertia::render('certificados/show', [
            'certificado' => [
                'id' => $certificado->id,
                'codigo' => $certificado->codigo,
                'tipo' => $certificado->tipo->value,
                'tipo_label' => $certificado->tipo->label(),
                'tipo_titulo' => $certificado->tipo->titulo(),
                'beneficiario_nombre' => $certificado->beneficiario_nombre,
                'beneficiario_documento' => $certificado->beneficiario_documento,
                'beneficiario_profesion' => $certificado->beneficiario_profesion,
                'cargo' => $certificado->cargo,
                'fecha_inicio' => $certificado->fecha_inicio?->format('Y-m-d'),
                'fecha_fin' => $certificado->fecha_fin?->format('Y-m-d'),
                'descripcion' => $certificado->descripcion,
                'lugar_emision' => $certificado->lugar_emision,
                'emisor_nombre' => $certificado->emisor_nombre,
                'emisor_cargo' => $certificado->emisor_cargo,
                'emisor_cip' => $certificado->emisor_cip,
                'fecha_emision' => $certificado->fecha_emision?->format('Y-m-d'),
                'hash_verificacion' => $certificado->hash_verificacion,
                'obra' => $certificado->obra ? [
                    'id' => $certificado->obra->id,
                    'codigo' => $certificado->obra->codigo,
                    'nombre' => $certificado->obra->nombre,
                    'entidad_contratante' => $certificado->obra->entidad_contratante,
                ] : null,
                'obra_nombre_libre' => $certificado->obra_nombre_libre,
                'obra_entidad_libre' => $certificado->obra_entidad_libre,
                'vigente' => $certificado->estaVigente(),
                'revocado_at' => $certificado->revocado_at?->toIso8601String(),
                'motivo_revocacion' => $certificado->motivo_revocacion,
                'url_verificacion' => route('verificar', $certificado->codigo),
                'url_pdf' => route('certificados.pdf', $certificado),
                'url_preview' => route('certificados.preview', $certificado),
            ],
        ]);
    }

    public function destroy(Certificado $certificado): RedirectResponse
    {
        $this->authorize('delete', $certificado);

        $certificado->delete();

        return redirect()
            ->route('certificados.index')
            ->with('success', 'Certificado eliminado.');
    }

    /**
     * "PDF": devolvemos el HTML print-ready con auto-print.
     * El navegador del usuario abre el diálogo de impresión → "Guardar como PDF".
     * Sin DomPDF, sin Browsershot, sin Chrome en el servidor.
     */
    public function pdf(Certificado $certificado, QrService $qr, BrandingService $branding): \Illuminate\Contracts\View\View
    {
        $this->authorize('view', $certificado);

        $datos = $this->datosTemplate($certificado, $qr, $branding);
        $datos['autoPrint'] = true;

        return view('certificados.template', $datos);
    }

    public function preview(Certificado $certificado, QrService $qr, BrandingService $branding): \Illuminate\Contracts\View\View
    {
        $this->authorize('view', $certificado);

        return view('certificados.template', $this->datosTemplate($certificado, $qr, $branding));
    }

    public function revocar(Certificado $certificado): RedirectResponse
    {
        $this->authorize('delete', $certificado);

        $certificado->update([
            'revocado_at' => now(),
            'motivo_revocacion' => request('motivo'),
        ]);

        // Notificar in-app a todos los administradores (auditoría interna).
        $admins = User::role(RolGlobal::rolesAdministrativos())->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new CertificadoRevocadoNotif($certificado));
        }

        return back()->with('success', 'Certificado revocado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function datosTemplate(Certificado $certificado, QrService $qr, BrandingService $branding): array
    {
        $certificado->loadMissing('obra');

        $url = route('verificar', $certificado->codigo);
        $logoPath = public_path('brand/rnfc-logo.png');
        $logoBase64 = is_file($logoPath)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath))
            : null;

        return [
            'certificado' => $certificado,
            'qrDataUri' => $qr->dataUri($url, 240),
            'urlVerificacion' => $url,
            'logoBase64' => $logoBase64,
            'branding' => $branding->dataUris(),
            'densidad' => $this->calcularDensidad($certificado),
        ];
    }

    /**
     * Calcula la densidad del certificado para que siempre quepa en 1 página.
     * Devuelve 'baja' (más aire), 'media' (estándar), 'alta' (compacto).
     */
    private function calcularDensidad(Certificado $certificado): string
    {
        $caracteres = mb_strlen((string) $certificado->beneficiario_nombre)
            + mb_strlen((string) $certificado->obra_nombre_efectivo)
            + mb_strlen((string) $certificado->obra_entidad_efectiva)
            + mb_strlen((string) $certificado->cargo)
            + mb_strlen((string) $certificado->descripcion);

        if ($caracteres > 280) {
            return 'alta';
        }
        if ($caracteres > 150) {
            return 'media';
        }

        return 'baja';
    }
}
