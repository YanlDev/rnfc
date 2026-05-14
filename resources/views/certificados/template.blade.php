<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $certificado->codigo }} — {{ $certificado->tipo->titulo() }}</title>
    <style>
        @page { margin: 0; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: auto; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            background: #ffffff;
        }

        /* ====================================================
           BASE COMPACTA — Opción 3
           Tamaños conservadores que SIEMPRE caben en 1 página.
           ==================================================== */
        .pagina {
            position: relative;
            width: 210mm;
            padding: 18mm 20mm 14mm;
            background: #ffffff;
            page-break-inside: avoid;
            page-break-after: avoid;
        }

        /* HEADER */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 7mm;
        }
        .header-cell { display: table-cell; vertical-align: middle; }
        .header-logo { width: 50%; text-align: left; }
        .header-iso  { width: 50%; text-align: right; white-space: nowrap; }
        .logo-principal { height: 14mm; }
        .iso-logo {
            height: 11mm; width: 11mm;
            object-fit: contain;
            margin-left: 2mm;
            vertical-align: middle;
        }
        .iso-placeholder {
            display: inline-block;
            width: 11mm; height: 11mm;
            margin-left: 2mm;
            border: 1px dashed #d1d5db;
            color: #9ca3af;
            font-size: 5pt;
            text-align: center;
            line-height: 11mm;
            vertical-align: middle;
        }

        /* CÓDIGO */
        .codigo {
            text-align: right;
            font-size: 8pt;
            color: #6b7280;
            letter-spacing: 0.8pt;
            margin-bottom: 7mm;
        }
        .codigo strong {
            font-family: 'Courier New', monospace;
            color: #1f2937;
            font-size: 9pt;
            letter-spacing: 0;
        }

        /* TÍTULO */
        .titulo {
            font-size: 28pt;
            color: #1f2937;
            font-weight: bold;
            letter-spacing: 1.2pt;
            text-align: center;
            margin-bottom: 7mm;
            line-height: 1.1;
        }

        /* BENEFICIARIO */
        .otorgado-a {
            text-align: center;
            font-size: 9pt;
            color: #6b7280;
            letter-spacing: 2pt;
            text-transform: uppercase;
            margin-bottom: 4mm;
        }
        .beneficiario {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            color: #0b2545;
            letter-spacing: 0.8pt;
            margin-bottom: 2mm;
            line-height: 1.2;
        }
        .beneficiario-meta {
            text-align: center;
            font-size: 9pt;
            color: #4b5563;
            margin-bottom: 7mm;
        }

        /* CUERPO */
        .cuerpo {
            font-size: 10.5pt;
            line-height: 1.65;
            text-align: justify;
            color: #1f2937;
            margin-bottom: 4mm;
        }
        .cuerpo p { margin-bottom: 2.5mm; }
        .cuerpo strong { color: #1f2937; }

        /* LUGAR Y FECHA */
        .lugar-fecha {
            text-align: right;
            font-size: 9.5pt;
            color: #4b5563;
            margin-top: 5mm;
            margin-bottom: 7mm;
        }

        /* FIRMA Y QR */
        .pie {
            display: table;
            width: 100%;
            margin-top: 4mm;
        }
        .pie-firma {
            display: table-cell;
            width: 65%;
            vertical-align: bottom;
            text-align: center;
            padding-right: 5mm;
        }
        .firma-img {
            max-height: 18mm;
            max-width: 55mm;
            margin: 0 auto -1.5mm;
            display: block;
        }
        .firma-img-placeholder { height: 16mm; }
        .firma-linea {
            border-top: 1px solid #1f2937;
            margin: 0 16mm 1.2mm;
        }
        .firma-nombre {
            font-size: 10pt;
            font-weight: bold;
            color: #1f2937;
        }
        .firma-cargo {
            font-size: 8.5pt;
            color: #6b7280;
            margin-top: 0.5mm;
        }
        .pie-qr {
            display: table-cell;
            width: 35%;
            vertical-align: bottom;
            text-align: right;
        }
        .qr-img {
            width: 23mm; height: 23mm;
            display: inline-block;
        }
        .qr-leyenda {
            font-size: 6.5pt;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.7pt;
            margin-top: 1mm;
        }
        .qr-url {
            font-size: 6pt;
            color: #9ca3af;
            font-family: 'Courier New', monospace;
            margin-top: 0.4mm;
        }

        /* SELLO REVOCADO */
        .estado-revocado {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 72pt;
            color: rgba(193, 39, 45, 0.12);
            border: 5px solid rgba(193, 39, 45, 0.18);
            padding: 3.5mm 10mm;
            font-weight: bold;
            letter-spacing: 5pt;
            z-index: 3;
        }

        /* ====================================================
           OPCIÓN 1 — Densidad automática según contenido.
           "baja"  = contenido corto  → más aire visual
           "media" = contenido medio  → base (definida arriba)
           "alta"  = contenido largo  → todo compacto
           ==================================================== */

        /* Densidad BAJA — contenido corto: amplificamos */
        .densidad-baja .titulo            { font-size: 32pt; margin-bottom: 9mm; }
        .densidad-baja .beneficiario      { font-size: 22pt; margin-bottom: 3mm; }
        .densidad-baja .beneficiario-meta { font-size: 10pt; margin-bottom: 9mm; }
        .densidad-baja .otorgado-a        { font-size: 10pt; margin-bottom: 5mm; }
        .densidad-baja .cuerpo            { font-size: 11.5pt; line-height: 1.8; margin-bottom: 5mm; }
        .densidad-baja .cuerpo p          { margin-bottom: 3.5mm; }
        .densidad-baja .header            { margin-bottom: 9mm; }
        .densidad-baja .codigo            { margin-bottom: 9mm; }
        .densidad-baja .lugar-fecha       { margin-top: 7mm; margin-bottom: 9mm; }

        /* Densidad ALTA — contenido largo: reducimos */
        .densidad-alta .pagina            { padding: 14mm 16mm 11mm; }
        .densidad-alta .titulo            { font-size: 22pt; margin-bottom: 5mm; letter-spacing: 0.8pt; }
        .densidad-alta .beneficiario      { font-size: 15pt; margin-bottom: 1.5mm; }
        .densidad-alta .beneficiario-meta { font-size: 8.5pt; margin-bottom: 5mm; }
        .densidad-alta .otorgado-a        { font-size: 8pt; margin-bottom: 3mm; letter-spacing: 1.5pt; }
        .densidad-alta .cuerpo            { font-size: 9.5pt; line-height: 1.5; margin-bottom: 3mm; }
        .densidad-alta .cuerpo p          { margin-bottom: 2mm; }
        .densidad-alta .header            { margin-bottom: 5mm; }
        .densidad-alta .codigo            { margin-bottom: 5mm; }
        .densidad-alta .logo-principal    { height: 12mm; }
        .densidad-alta .iso-logo,
        .densidad-alta .iso-placeholder   { height: 10mm; width: 10mm; line-height: 10mm; }
        .densidad-alta .lugar-fecha       { margin-top: 3mm; margin-bottom: 5mm; font-size: 9pt; }
        .densidad-alta .firma-img         { max-height: 15mm; }
        .densidad-alta .qr-img            { width: 20mm; height: 20mm; }

        /* Densidad EXTREMA — fallback para contenido muy largo */
        .densidad-extrema .pagina            { padding: 10mm 12mm 8mm; }
        .densidad-extrema .titulo            { font-size: 18pt; margin-bottom: 3mm; letter-spacing: 0.5pt; }
        .densidad-extrema .beneficiario      { font-size: 13pt; margin-bottom: 1mm; }
        .densidad-extrema .beneficiario-meta { font-size: 7.5pt; margin-bottom: 3mm; }
        .densidad-extrema .otorgado-a        { font-size: 7pt; margin-bottom: 2mm; letter-spacing: 1pt; }
        .densidad-extrema .cuerpo            { font-size: 8.5pt; line-height: 1.4; margin-bottom: 2mm; }
        .densidad-extrema .cuerpo p          { margin-bottom: 1.5mm; }
        .densidad-extrema .header            { margin-bottom: 3mm; }
        .densidad-extrema .codigo            { margin-bottom: 3mm; font-size: 7pt; }
        .densidad-extrema .codigo strong     { font-size: 8pt; }
        .densidad-extrema .logo-principal    { height: 10mm; }
        .densidad-extrema .iso-logo,
        .densidad-extrema .iso-placeholder   { height: 8mm; width: 8mm; line-height: 8mm; }
        .densidad-extrema .lugar-fecha       { margin-top: 2mm; margin-bottom: 3mm; font-size: 8pt; }
        .densidad-extrema .firma-img         { max-height: 12mm; }
        .densidad-extrema .qr-img            { width: 17mm; height: 17mm; }
        .densidad-extrema .firma-nombre      { font-size: 9pt; }
        .densidad-extrema .firma-cargo       { font-size: 7.5pt; }
    </style>
</head>
<body class="densidad-{{ $densidad ?? 'media' }}">
    @php
        $obraNombre = $certificado->obra_nombre_efectivo;
        $entidad = $certificado->obra_entidad_efectiva;
        $cargo = $certificado->cargo ?: $certificado->tipo->label();
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

    <div class="pagina">
        @if (! $certificado->estaVigente())
            <div class="estado-revocado">REVOCADO</div>
        @endif

        {{-- Header: logo + ISOs --}}
        <div class="header">
            <div class="header-cell header-logo">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="RNFC" class="logo-principal">
                @endif
            </div>
            <div class="header-cell header-iso">
                @foreach (['iso1', 'iso2', 'iso3'] as $iso)
                    @if (! empty($branding[$iso]))
                        <img src="{{ $branding[$iso] }}" alt="{{ strtoupper($iso) }}" class="iso-logo">
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
                    DNI N° {{ $certificado->beneficiario_documento }}
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
                    <div class="firma-img-placeholder"></div>
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
    </div>
</body>
</html>
