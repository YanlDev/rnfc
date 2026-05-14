<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $certificado->codigo }} — {{ $certificado->tipo->titulo() }}</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    /* =========================================================
       PRINT — A4 sin márgenes del navegador
       ========================================================= */
    @page {
        size: A4 portrait;
        margin: 0;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    html, body {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        color: #1f2937;
        background: #e5e7eb;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Barra de acciones (solo en pantalla) */
    .toolbar {
        position: fixed;
        top: 16px;
        right: 16px;
        z-index: 100;
        display: flex;
        gap: 8px;
    }
    .toolbar button, .toolbar a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: transform .15s, box-shadow .15s;
    }
    .toolbar button:hover, .toolbar a:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    }
    .btn-print  { background: #0b2545; color: #fff; }
    .btn-back   { background: #fff; color: #0b2545; border: 1px solid #d1d5db !important; }

    /* Lienzo: imita una hoja A4 en pantalla */
    .lienzo {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 32px 16px;
        min-height: 100vh;
    }

    .pagina {
        position: relative;
        width: 210mm;
        height: 297mm;
        background: #ffffff;
        padding: 18mm 22mm;
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        overflow: hidden;
    }

    /* Wrapper que el JS escalará si el contenido excede 297mm */
    .fit {
        transform-origin: top left;
        width: 100%;
    }

    /* =========================================================
       HEADER
       ========================================================= */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8mm;
    }
    .logo-principal { height: 16mm; }
    .header-iso { display: flex; gap: 3mm; align-items: center; }
    .iso-logo {
        width: 14mm;
        height: 14mm;
        object-fit: contain;
    }
    .iso-placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 14mm; height: 14mm;
        border: 1px dashed #d1d5db;
        color: #9ca3af;
        font-size: 8pt;
        font-weight: 600;
    }

    /* =========================================================
       CÓDIGO
       ========================================================= */
    .codigo {
        text-align: right;
        font-size: 9pt;
        color: #6b7280;
        margin-bottom: 9mm;
    }
    .codigo strong {
        color: #111827;
        font-family: 'Inter', monospace;
        font-weight: 700;
    }

    /* =========================================================
       TÍTULO
       ========================================================= */
    .titulo {
        text-align: center;
        font-size: 32pt;
        color: #1f2937;
        font-weight: 800;
        letter-spacing: 1pt;
        line-height: 1.1;
        margin-bottom: 9mm;
    }

    /* =========================================================
       BENEFICIARIO
       ========================================================= */
    .otorgado-a {
        text-align: center;
        font-size: 9pt;
        color: #6b7280;
        letter-spacing: 3pt;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 5mm;
    }
    .beneficiario {
        text-align: center;
        font-size: 22pt;
        font-weight: 700;
        color: #0b2545;
        letter-spacing: 0.5pt;
        margin-bottom: 2.5mm;
        line-height: 1.2;
    }
    .beneficiario-meta {
        text-align: center;
        font-size: 10pt;
        color: #4b5563;
        margin-bottom: 8mm;
        font-weight: 500;
    }
    .beneficiario-meta strong { color: #0b2545; font-weight: 700; }

    /* =========================================================
       CUERPO
       ========================================================= */
    .cuerpo {
        font-size: 11pt;
        line-height: 1.75;
        text-align: justify;
        color: #374151;
        margin-bottom: 5mm;
        padding: 0 6mm;
    }
    .cuerpo p { margin-bottom: 3mm; }
    .cuerpo strong { color: #0b2545; font-weight: 700; }

    /* =========================================================
       LUGAR Y FECHA
       ========================================================= */
    .lugar-fecha {
        text-align: right;
        font-size: 10pt;
        color: #4b5563;
        font-style: italic;
        margin-top: 6mm;
        margin-bottom: 8mm;
        padding-right: 6mm;
    }

    /* =========================================================
       FIRMA + QR
       ========================================================= */
    .pie {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: 5mm;
    }
    .pie-firma {
        flex: 0 0 62%;
        text-align: center;
        padding-right: 5mm;
    }
    .firma-img {
        max-height: 22mm;
        max-width: 60mm;
        margin: 0 auto -1mm;
        display: block;
    }
    .firma-placeholder { height: 20mm; }
    .firma-linea {
        border-top: 1.2px solid #0b2545;
        margin: 0 18mm 1.5mm;
    }
    .firma-nombre {
        font-size: 10.5pt;
        font-weight: 700;
        color: #0b2545;
    }
    .firma-cargo {
        font-size: 8.5pt;
        color: #6b7280;
        margin-top: 0.5mm;
        font-weight: 500;
    }
    .pie-qr {
        flex: 0 0 32%;
        text-align: right;
    }
    .qr-img {
        width: 24mm;
        height: 24mm;
        display: inline-block;
    }
    .qr-leyenda {
        font-size: 7pt;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.8pt;
        margin-top: 1.5mm;
    }
    .qr-url {
        font-size: 6.5pt;
        color: #9ca3af;
        font-family: 'Inter', monospace;
        margin-top: 0.5mm;
        word-break: break-all;
    }

    /* =========================================================
       SELLO REVOCADO
       ========================================================= */
    .estado-revocado {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%) rotate(-25deg);
        font-size: 80pt;
        color: rgba(193, 39, 45, 0.12);
        border: 6px solid rgba(193, 39, 45, 0.18);
        padding: 4mm 12mm;
        font-weight: 800;
        letter-spacing: 6pt;
        z-index: 3;
        pointer-events: none;
    }

    /* =========================================================
       PRINT: ocultar UI, ajustar lienzo
       ========================================================= */
    @media print {
        html, body { background: #ffffff !important; }
        .toolbar { display: none !important; }
        .lienzo {
            padding: 0;
            min-height: 0;
            display: block;
        }
        .pagina {
            box-shadow: none;
            margin: 0;
        }
    }
</style>
</head>

<body>

@php
    $obraNombre = $certificado->obra_nombre_efectivo;
    $entidad = $certificado->obra_entidad_efectiva;
    $cargo = $certificado->cargo ?: $certificado->tipo->cargoPorDefecto();
    $fInicio = $certificado->fecha_inicio
        ? \Carbon\Carbon::parse($certificado->fecha_inicio)->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
        : null;
    $fFin = $certificado->fecha_fin
        ? \Carbon\Carbon::parse($certificado->fecha_fin)->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
        : null;
    $fEmision = $certificado->fecha_emision
        ? \Carbon\Carbon::parse($certificado->fecha_emision)->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
        : '';
@endphp

{{-- Toolbar (oculta al imprimir) --}}
<div class="toolbar">
    <a href="{{ url()->previous() }}" class="btn-back">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Volver
    </a>
    <button type="button" onclick="window.print()" class="btn-print">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z"/></svg>
        Imprimir / Guardar PDF
    </button>
</div>

<div class="lienzo">
<div class="pagina">

    @if (! $certificado->estaVigente())
        <div class="estado-revocado">REVOCADO</div>
    @endif

    <div class="fit" id="fit">

    {{-- Header --}}
    <div class="header">
        <div class="header-logo">
            @if ($logoBase64)
                <img src="{{ $logoBase64 }}" class="logo-principal" alt="RNFC">
            @endif
        </div>
        <div class="header-iso">
            @foreach (['iso1', 'iso2', 'iso3'] as $iso)
                @if (! empty($branding[$iso]))
                    <img src="{{ $branding[$iso] }}" class="iso-logo" alt="{{ strtoupper($iso) }}">
                @else
                    <span class="iso-placeholder">{{ strtoupper($iso) }}</span>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Código --}}
    <div class="codigo">
        N° de Certificado: <strong>{{ $certificado->codigo }}</strong>
    </div>

    {{-- Título --}}
    <div class="titulo">{{ mb_strtoupper($certificado->tipo->titulo()) }}</div>

    {{-- Beneficiario --}}
    <div class="otorgado-a">Se otorga a</div>
    <div class="beneficiario">{{ mb_strtoupper($certificado->beneficiario_nombre) }}</div>
    @if ($certificado->beneficiario_documento || $certificado->beneficiario_profesion)
        <div class="beneficiario-meta">
            @if ($certificado->beneficiario_documento)
                DNI N° <strong>{{ $certificado->beneficiario_documento }}</strong>
            @endif
            @if ($certificado->beneficiario_documento && $certificado->beneficiario_profesion) · @endif
            @if ($certificado->beneficiario_profesion)
                {{ $certificado->beneficiario_profesion }}
            @endif
        </div>
    @else
        <div style="height: 8mm;"></div>
    @endif

    {{-- Cuerpo --}}
    <div class="cuerpo">
        @switch($certificado->tipo)
            @case(\App\Enums\TipoCertificado::Trabajador)
            @case(\App\Enums\TipoCertificado::Residente)
            @case(\App\Enums\TipoCertificado::Supervisor)
            @case(\App\Enums\TipoCertificado::Especialista)
                <p>
                    Por haber laborado en nuestra empresa como
                    <strong>{{ mb_strtoupper($cargo) }}</strong>{{ $obraNombre ? ',' : '.' }}
                    @if ($obraNombre)
                        en la ejecución de la obra <strong>{{ mb_strtoupper($obraNombre) }}</strong>{{ $entidad ? ',' : '' }}
                        @if ($entidad)
                            ejecutada para <strong>{{ $entidad }}</strong>,
                        @endif
                    @endif
                    @if ($fInicio && $fFin)
                        durante el período comprendido entre el <strong>{{ $fInicio }}</strong>
                        y el <strong>{{ $fFin }}</strong>,
                    @endif
                    demostrando durante su permanencia puntualidad, responsabilidad,
                    honestidad y dedicación en las labores que le fueron encomendadas.
                </p>
                @break

            @case(\App\Enums\TipoCertificado::Capacitacion)
                <p>
                    Por haber participado satisfactoriamente en la capacitación denominada
                    <strong>{{ $cargo }}</strong>,
                    @if ($fInicio && $fFin)
                        realizada entre el <strong>{{ $fInicio }}</strong>
                        y el <strong>{{ $fFin }}</strong>,
                    @endif
                    cumpliendo con la totalidad del programa académico establecido.
                </p>
                @break

            @case(\App\Enums\TipoCertificado::Participacion)
                <p>
                    Por su participación
                    @if ($certificado->cargo) en calidad de <strong>{{ $certificado->cargo }}</strong> @endif
                    @if ($obraNombre)
                        en el proyecto <strong>{{ mb_strtoupper($obraNombre) }}</strong>,
                    @endif
                    demostrando profesionalismo y compromiso durante toda su intervención.
                </p>
                @break

            @case(\App\Enums\TipoCertificado::ServiciosProfesionales)
                <p>
                    Por haber prestado servicios profesionales
                    @if ($certificado->cargo) en calidad de <strong>{{ $certificado->cargo }}</strong> @endif
                    @if ($obraNombre)
                        para la obra <strong>{{ mb_strtoupper($obraNombre) }}</strong>
                    @endif
                    @if ($fInicio && $fFin)
                        , entre el <strong>{{ $fInicio }}</strong>
                        y el <strong>{{ $fFin }}</strong>
                    @endif
                    , cumpliendo a cabalidad con los términos del encargo recibido.
                </p>
                @break
        @endswitch

        @if ($certificado->descripcion)
            <p>{{ $certificado->descripcion }}</p>
        @endif

        <p>
            Se expide la presente a solicitud del interesado, para los fines que crea convenientes.
        </p>
    </div>

    {{-- Lugar y fecha --}}
    <div class="lugar-fecha">
        {{ $certificado->lugar_emision }}, {{ $fEmision }}.
    </div>

    {{-- Firma + QR --}}
    <div class="pie">
        <div class="pie-firma">
            @if (! empty($branding['firma']))
                <img src="{{ $branding['firma'] }}" alt="Firma" class="firma-img">
            @else
                <div class="firma-placeholder"></div>
            @endif
            <div class="firma-linea"></div>
            <div class="firma-nombre">{{ $certificado->emisor_nombre }}</div>
            <div class="firma-cargo">
                {{ $certificado->emisor_cargo }}
                @if ($certificado->emisor_cip) · CIP {{ $certificado->emisor_cip }} @endif
            </div>
        </div>
        <div class="pie-qr">
            <img src="{{ $qrDataUri }}" alt="QR de verificación" class="qr-img">
            <div class="qr-leyenda">Verifica autenticidad</div>
            <div class="qr-url">{{ str_replace(['http://','https://'], '', $urlVerificacion) }}</div>
        </div>
    </div>

    </div>{{-- /.fit --}}
</div>
</div>

<script>
    (function () {
        // Auto-fit: si el contenido excede los 297mm disponibles, escalar suavemente.
        const fit = document.getElementById('fit');
        const pagina = document.querySelector('.pagina');

        function ajustar() {
            if (!fit || !pagina) return;
            fit.style.transform = '';
            fit.style.width = '100%';

            // altura disponible = pagina interior (sin padding)
            const cs = getComputedStyle(pagina);
            const disponible = pagina.clientHeight
                - parseFloat(cs.paddingTop)
                - parseFloat(cs.paddingBottom);
            const real = fit.scrollHeight;

            if (real > disponible) {
                let escala = disponible / real;
                if (escala < 0.7) escala = 0.7;
                if (escala > 0.99) escala = 0.99;
                fit.style.transform = 'scale(' + escala + ')';
                fit.style.width = (100 / escala) + '%';
            }
        }

        if (document.readyState === 'complete') ajustar();
        else window.addEventListener('load', ajustar);
        window.addEventListener('resize', ajustar);

        // Auto-print si se llegó por la ruta /pdf (Blade inyecta el flag).
        @if (!empty($autoPrint))
            window.addEventListener('load', () => {
                setTimeout(() => window.print(), 500);
            });
        @endif
    })();
</script>

</body>
</html>
