<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $certificado->codigo }} — {{ $certificado->tipo->titulo() }}</title>
    <style>
        @page { margin: 0; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            background: #ffffff;
            font-size: 11pt;
            line-height: 1.5;
        }
        .pagina {
            position: relative;
            width: 210mm;
            padding: 22mm 22mm 18mm;
            background: #ffffff;
            page-break-inside: avoid;
            page-break-after: avoid;
        }
        html, body { height: auto; }

        /* === HEADER === */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 10mm;
        }
        .header-cell { display: table-cell; vertical-align: middle; }
        .header-logo { width: 50%; text-align: left; }
        .header-iso  { width: 50%; text-align: right; white-space: nowrap; }

        .logo-principal { height: 16mm; }
        .iso-logo {
            height: 13mm;
            width: 13mm;
            object-fit: contain;
            margin-left: 2.5mm;
            vertical-align: middle;
        }
        .iso-placeholder {
            display: inline-block;
            width: 13mm; height: 13mm;
            margin-left: 2.5mm;
            border: 1px dashed #d1d5db;
            color: #9ca3af;
            font-size: 5.5pt;
            text-align: center;
            line-height: 13mm;
            vertical-align: middle;
        }

        /* === CÓDIGO === */
        .codigo {
            text-align: right;
            font-size: 8.5pt;
            color: #6b7280;
            letter-spacing: 1pt;
            margin-bottom: 10mm;
        }
        .codigo strong {
            font-family: 'Courier New', monospace;
            color: #1f2937;
            font-size: 9.5pt;
            letter-spacing: 0;
        }

        /* === TÍTULO === */
        .titulo {
            font-size: 34pt;
            color: #1f2937;
            font-weight: bold;
            letter-spacing: 1.5pt;
            text-align: center;
            margin-bottom: 9mm;
        }

        /* === BENEFICIARIO === */
        .otorgado-a {
            text-align: center;
            font-size: 10pt;
            color: #6b7280;
            letter-spacing: 2.5pt;
            text-transform: uppercase;
            margin-bottom: 6mm;
        }
        .beneficiario {
            text-align: center;
            font-size: 22pt;
            font-weight: bold;
            color: #0b2545;
            letter-spacing: 1pt;
            margin-bottom: 3mm;
        }
        .beneficiario-meta {
            text-align: center;
            font-size: 10pt;
            color: #4b5563;
            margin-bottom: 10mm;
        }

        /* === CUERPO === */
        .cuerpo {
            font-size: 11.5pt;
            line-height: 1.85;
            text-align: justify;
            color: #1f2937;
            margin-bottom: 6mm;
        }
        .cuerpo p { margin-bottom: 3.5mm; }
        .cuerpo strong { color: #1f2937; }

        /* === LUGAR Y FECHA === */
        .lugar-fecha {
            text-align: right;
            font-size: 10pt;
            color: #4b5563;
            margin-top: 8mm;
            margin-bottom: 10mm;
        }

        /* === FIRMA Y QR === */
        .pie {
            display: table;
            width: 100%;
            margin-top: 6mm;
        }
        .pie-firma {
            display: table-cell;
            width: 65%;
            vertical-align: bottom;
            text-align: center;
            padding-right: 6mm;
        }
        .firma-img {
            max-height: 22mm;
            max-width: 60mm;
            margin: 0 auto -2mm;
            display: block;
        }
        .firma-img-placeholder { height: 20mm; }
        .firma-linea {
            border-top: 1px solid #1f2937;
            margin: 0 18mm 1.5mm;
        }
        .firma-nombre {
            font-size: 10.5pt;
            font-weight: bold;
            color: #1f2937;
        }
        .firma-cargo {
            font-size: 9pt;
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
            width: 26mm;
            height: 26mm;
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
            font-family: 'Courier New', monospace;
            margin-top: 0.5mm;
        }

        /* === SELLO REVOCADO === */
        .estado-revocado {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            font-size: 80pt;
            color: rgba(193, 39, 45, 0.12);
            border: 6px solid rgba(193, 39, 45, 0.18);
            padding: 4mm 12mm;
            font-weight: bold;
            letter-spacing: 6pt;
            z-index: 3;
        }
    </style>
</head>
<body>
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
