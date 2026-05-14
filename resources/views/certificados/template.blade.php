<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $certificado->codigo }} — {{ $certificado->tipo->titulo() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        @page { margin: 0; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            width: 210mm;
            height: 297mm;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #1f2937;
            background: #ffffff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .pagina {
            position: relative;
            width: 210mm;
            height: 297mm;
            padding: 20mm 22mm 16mm;
            background: #ffffff;
            overflow: hidden;
        }

        /* Wrapper que se escala automáticamente vía JS si el contenido desborda */
        .fit-wrapper {
            transform-origin: top center;
            width: 100%;
        }

        /* === ORNAMENTACIÓN DE FONDO === */
        .ornamento-superior, .ornamento-inferior {
            position: absolute;
            left: 0; right: 0;
            height: 8mm;
            background: linear-gradient(90deg, #0b2545 0%, #145694 50%, #0b2545 100%);
        }
        .ornamento-superior { top: 0; }
        .ornamento-inferior { bottom: 0; }
        .ornamento-superior::after, .ornamento-inferior::after {
            content: '';
            position: absolute;
            left: 22mm; right: 22mm;
            height: 1px;
            background: rgba(212, 175, 55, 0.6);
        }
        .ornamento-superior::after { bottom: -3mm; }
        .ornamento-inferior::after { top: -3mm; }

        /* === HEADER === */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 9mm;
        }
        .header-logo { display: flex; align-items: center; }
        .logo-principal { height: 16mm; }
        .header-iso { display: flex; align-items: center; gap: 3mm; }
        .iso-logo {
            height: 14mm;
            width: 14mm;
            object-fit: contain;
        }
        .iso-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 14mm; height: 14mm;
            border: 1px dashed #d1d5db;
            color: #9ca3af;
            font-size: 6pt;
            font-weight: 600;
        }

        /* === CÓDIGO === */
        .codigo-row {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 2mm;
            font-size: 9pt;
            color: #6b7280;
            letter-spacing: 0.8pt;
            margin-bottom: 8mm;
        }
        .codigo-row strong {
            font-family: 'Inter', monospace;
            color: #0b2545;
            font-weight: 700;
            font-size: 10pt;
            letter-spacing: 0;
            padding: 1mm 3mm;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border-radius: 2mm;
        }

        /* === TÍTULO === */
        .titulo-wrap { text-align: center; margin-bottom: 9mm; }
        .titulo-eyebrow {
            display: inline-block;
            font-size: 8pt;
            letter-spacing: 4pt;
            color: #D4AF37;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 3mm;
            padding: 0 8mm;
            position: relative;
        }
        .titulo-eyebrow::before, .titulo-eyebrow::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 6mm;
            height: 1px;
            background: #D4AF37;
        }
        .titulo-eyebrow::before { right: 100%; }
        .titulo-eyebrow::after  { left: 100%; }

        .titulo {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 36pt;
            color: #0b2545;
            font-weight: 800;
            letter-spacing: 1pt;
            line-height: 1.05;
        }

        /* === BENEFICIARIO === */
        .otorgado-a {
            text-align: center;
            font-size: 9pt;
            color: #6b7280;
            letter-spacing: 3pt;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5mm;
            position: relative;
        }
        .otorgado-a::before, .otorgado-a::after {
            content: '';
            display: inline-block;
            width: 14mm;
            height: 1px;
            background: #d1d5db;
            vertical-align: middle;
            margin: 0 4mm;
        }
        .beneficiario {
            text-align: center;
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 26pt;
            font-weight: 700;
            color: #0b2545;
            letter-spacing: 0.5pt;
            margin-bottom: 2.5mm;
            line-height: 1.15;
        }
        .beneficiario-meta {
            text-align: center;
            font-size: 9.5pt;
            color: #4b5563;
            margin-bottom: 9mm;
            font-weight: 500;
        }
        .beneficiario-meta strong { color: #0b2545; font-weight: 700; }

        /* === CUERPO === */
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

        /* === LUGAR Y FECHA === */
        .lugar-fecha {
            text-align: right;
            font-size: 10pt;
            color: #4b5563;
            font-style: italic;
            margin-top: 6mm;
            margin-bottom: 8mm;
            padding-right: 6mm;
        }

        /* === FIRMA Y QR === */
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
        .firma-img-placeholder { height: 20mm; }
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
            text-align: center;
        }
        .qr-box {
            display: inline-block;
            padding: 2mm;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 2mm;
        }
        .qr-img {
            width: 26mm;
            height: 26mm;
            display: block;
        }
        .qr-leyenda {
            font-size: 7pt;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1pt;
            margin-top: 2mm;
            font-weight: 600;
        }
        .qr-url {
            font-size: 6.5pt;
            color: #9ca3af;
            font-family: 'Inter', monospace;
            margin-top: 0.5mm;
        }

        /* === SELLO REVOCADO === */
        .estado-revocado {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-22deg);
            font-size: 88pt;
            color: rgba(193, 39, 45, 0.10);
            border: 8px solid rgba(193, 39, 45, 0.15);
            padding: 6mm 18mm;
            font-weight: 800;
            letter-spacing: 8pt;
            font-family: 'Playfair Display', serif;
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
        <div class="ornamento-superior"></div>
        <div class="ornamento-inferior"></div>

        @if (! $certificado->estaVigente())
            <div class="estado-revocado">REVOCADO</div>
        @endif

        <div class="fit-wrapper" id="fitWrapper">
            {{-- Header --}}
            <div class="header">
                <div class="header-logo">
                    @if ($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="RNFC" class="logo-principal">
                    @endif
                </div>
                <div class="header-iso">
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
            <div class="codigo-row">
                <span>N° de Certificado</span>
                <strong>{{ $certificado->codigo }}</strong>
            </div>

            {{-- Título --}}
            <div class="titulo-wrap">
                <div class="titulo-eyebrow">Certificación oficial</div>
                <div class="titulo">{{ mb_strtoupper($certificado->tipo->titulo()) }}</div>
            </div>

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
                    <div class="qr-box">
                        <img src="{{ $qrDataUri }}" alt="QR de verificación" class="qr-img">
                    </div>
                    <div class="qr-leyenda">Verifica autenticidad</div>
                    <div class="qr-url">{{ str_replace(['http://','https://'], '', $urlVerificacion) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Auto-fit: si el contenido desborda el alto disponible, lo escalamos sutilmente
         para que siempre quepa en 1 página A4. Requiere Browsershot (Chrome headless). --}}
    <script>
        (function () {
            const wrapper = document.getElementById('fitWrapper');
            const pagina = document.querySelector('.pagina');
            if (!wrapper || !pagina) return;

            // Padding en mm convertido a px (1mm ≈ 3.7795275591 px @ 96dpi)
            const mmToPx = (mm) => mm * 3.7795275591;
            const padTop = mmToPx(20);
            const padBottom = mmToPx(16);
            const pageHeight = mmToPx(297);
            const available = pageHeight - padTop - padBottom;

            // Mide el contenido natural
            const natural = wrapper.scrollHeight;
            if (natural <= available) return; // ya cabe, no hacemos nada

            // Calcula el factor de escala con un pequeño margen de seguridad
            let scale = available / natural;
            if (scale > 0.99) scale = 0.99;
            if (scale < 0.72) scale = 0.72; // suelo: nunca tan pequeño que sea ilegible

            wrapper.style.transform = `scale(${scale})`;
            wrapper.style.transformOrigin = 'top center';
            wrapper.style.width = (100 / scale) + '%';
            wrapper.style.marginLeft = ((100 - 100 / scale) / 2) + '%';
        })();
    </script>
</body>
</html>
