<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Verificación de certificado · RNFC Consultor de Obras</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Barlow:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --azul: #0b2545;
            --azul-2: #145694;
            --amarillo: #ffd21c;
            --verde: #15803d;
            --verde-bg: #ecfdf5;
            --rojo: #c1272d;
            --rojo-bg: #fef2f2;
            --gris-1: #1f2937;
            --gris-2: #6b7280;
            --gris-3: #9ca3af;
            --borde: #e5e7eb;
            --bg: #f8fafc;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            min-height: 100vh;
            color: var(--gris-1);
            display: flex;
            flex-direction: column;
            font-feature-settings: 'cv02','cv03','cv04','cv11';
            letter-spacing: -0.005em;
        }

        /* === HEADER === */
        header.top {
            border-bottom: 1px solid var(--borde);
            background: white;
        }
        header.top .inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        header.top a.brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        header.top img { height: 60px; width: auto; }
        header.top .volver {
            font-size: 13px;
            color: var(--gris-2);
            text-decoration: none;
            font-weight: 500;
        }
        header.top .volver:hover { color: var(--azul-2); }

        /* === MAIN === */
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 48px 24px;
        }
        .card {
            background: white;
            border: 1px solid var(--borde);
            border-radius: 16px;
            max-width: 1100px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 20px 60px -20px rgba(11,37,69,0.15);
        }

        /* Layout horizontal: izquierda (status + código + isos) | derecha (detalles) */
        .grid-horizontal {
            display: grid;
            grid-template-columns: 360px 1fr;
        }
        @media (max-width: 900px) {
            .grid-horizontal { grid-template-columns: 1fr; }
        }
        .left-pane {
            background: linear-gradient(180deg, #fafbfc 0%, white 100%);
            border-right: 1px solid var(--borde);
            display: flex;
            flex-direction: column;
        }
        @media (max-width: 900px) {
            .left-pane { border-right: 0; border-bottom: 1px solid var(--borde); }
        }
        .right-pane {
            display: flex;
            flex-direction: column;
        }

        /* === STATUS BAR === */
        .status {
            padding: 28px 32px 24px;
            border-bottom: 1px solid var(--borde);
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .status-icon {
            flex-shrink: 0;
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .status-icon svg { width: 28px; height: 28px; stroke-width: 2.5; }
        .status.ok .status-icon { background: var(--verde-bg); color: var(--verde); }
        .status.revocado .status-icon { background: var(--rojo-bg); color: var(--rojo); }
        .status.error .status-icon { background: #f3f4f6; color: var(--gris-2); }
        .status-text { flex: 1; min-width: 0; }
        .status-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .status.ok .status-label { color: var(--verde); }
        .status.revocado .status-label { color: var(--rojo); }
        .status.error .status-label { color: var(--gris-2); }
        .status-title {
            font-family: 'Barlow', sans-serif;
            font-size: 21px;
            font-weight: 700;
            color: var(--gris-1);
            letter-spacing: -0.015em;
            line-height: 1.2;
        }
        .status-sub {
            font-size: 12.5px;
            color: var(--gris-2);
            margin-top: 4px;
            line-height: 1.45;
        }

        /* === CÓDIGO === */
        .codigo-wrap {
            padding: 22px 32px;
            border-bottom: 1px solid var(--borde);
        }
        .codigo-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--gris-2);
            margin-bottom: 6px;
        }
        .codigo-valor {
            font-family: 'Courier New', monospace;
            font-size: 19px;
            font-weight: 700;
            letter-spacing: 1.5px;
            color: var(--azul);
            word-break: break-all;
        }

        /* === DETAILS GRID === */
        .details {
            padding: 24px 32px;
        }
        .details + .details { padding-top: 0; }
        .section-title {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--gris-2);
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--borde);
        }
        dl.info {
            display: grid;
            grid-template-columns: max-content 1fr;
            gap: 10px 24px;
            font-size: 13.5px;
        }
        dl.info dt {
            color: var(--gris-2);
            font-weight: 500;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            align-self: center;
        }
        dl.info dd {
            color: var(--gris-1);
            font-weight: 500;
        }
        dl.info dd.featured {
            font-family: 'Barlow', sans-serif;
            font-size: 17px;
            font-weight: 700;
            color: var(--azul);
        }

        /* === ISO BADGES (panel izquierdo, abajo) === */
        .isos-block {
            margin-top: auto;
            padding: 22px 32px 26px;
            border-top: 1px solid var(--borde);
        }
        .isos-block .isos-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--gris-2);
            margin-bottom: 12px;
        }
        .isos-grid {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 14px;
            flex-wrap: wrap;
        }
        .isos-grid img {
            height: 58px;
            width: 58px;
            object-fit: contain;
        }

        /* === HASH FOOTER === */
        .hash-bar {
            margin-top: auto;
            padding: 12px 32px;
            background: #f9fafb;
            border-top: 1px solid var(--borde);
            font-size: 10.5px;
            color: var(--gris-2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .hash-bar strong { color: var(--gris-1); font-weight: 600; }
        .hash-bar code {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            color: var(--gris-1);
        }

        /* === EMPTY STATE (no encontrado) === */
        .empty {
            padding: 20px 40px 32px;
            text-align: center;
            font-size: 14px;
            color: var(--gris-2);
            line-height: 1.6;
        }
        .empty a { color: var(--azul-2); font-weight: 500; text-decoration: none; }
        .empty a:hover { text-decoration: underline; }

        .btn-volver {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            padding: 10px 18px;
            border: 1px solid var(--borde);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--gris-1);
            background: white;
            text-decoration: none;
            transition: border-color .15s, color .15s;
        }
        .btn-volver:hover { border-color: var(--azul-2); color: var(--azul-2); }

        /* === FOOTER === */
        footer.bottom {
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: var(--gris-2);
            border-top: 1px solid var(--borde);
            background: white;
        }
        footer.bottom a { color: var(--azul-2); text-decoration: none; font-weight: 500; }

        @media (max-width: 640px) {
            .status, .codigo-wrap, .details, .isos-block, .hash-bar, .empty { padding-left: 24px; padding-right: 24px; }
            dl.info { grid-template-columns: 1fr; gap: 4px 0; }
            dl.info dt { margin-top: 12px; }
            dl.info dt:first-child { margin-top: 0; }
        }
    </style>
</head>
<body>
    <header class="top">
        <div class="inner">
            <a href="/" class="brand" aria-label="RNFC Consultor de Obras">
                <img src="/brand/rnfc-logo.png" alt="RNFC Consultor de Obras">
            </a>
            <a href="{{ route('verificar.form') }}" class="volver">← Verificar otro</a>
        </div>
    </header>

    <main>
        <div class="card">
            @if (! $certificado)
                {{-- ============ NO ENCONTRADO ============ --}}
                <div class="status error">
                    <div class="status-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <circle cx="12" cy="16" r="0.5" fill="currentColor"/>
                        </svg>
                    </div>
                    <div class="status-text">
                        <div class="status-label">Sin coincidencias</div>
                        <div class="status-title">Certificado no encontrado</div>
                        <div class="status-sub">El código consultado no corresponde a ningún documento emitido por RNFC.</div>
                    </div>
                </div>
                <div class="codigo-wrap">
                    <div class="codigo-label">Código consultado</div>
                    <div class="codigo-valor">{{ $codigo }}</div>
                </div>
                <div class="empty">
                    Verifica que hayas ingresado el código correctamente.
                    Si crees que se trata de un error, escribe a
                    <a href="mailto:contacto@rnfcconsultoria.com">contacto@rnfcconsultoria.com</a>.
                    <br>
                    <a href="{{ route('verificar.form') }}" class="btn-volver">Intentar de nuevo</a>
                </div>

            @elseif (! $certificado->estaVigente())
                {{-- ============ REVOCADO (horizontal) ============ --}}
                <div class="grid-horizontal">
                    <div class="left-pane">
                        <div class="status revocado">
                            <div class="status-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                </svg>
                            </div>
                            <div class="status-text">
                                <div class="status-label">Anulado</div>
                                <div class="status-title">Certificado revocado</div>
                                <div class="status-sub">Este documento ya no es válido.</div>
                            </div>
                        </div>
                        <div class="codigo-wrap">
                            <div class="codigo-label">Código del certificado</div>
                            <div class="codigo-valor">{{ $certificado->codigo }}</div>
                        </div>
                        <div class="isos-block">
                            <div class="isos-label">Empresa Certificada</div>
                            <div class="isos-grid">
                                <img src="/brand/ISO 9001.png"  alt="ISO 9001:2015">
                                <img src="/brand/ISO 14001.png" alt="ISO 14001:2015">
                                <img src="/brand/ISO 37001.png" alt="ISO 37001:2025">
                            </div>
                        </div>
                    </div>
                    <div class="right-pane">
                        <div class="details">
                            <div class="section-title">Información del documento</div>
                            <dl class="info">
                                <dt>Tipo</dt>
                                <dd>{{ $certificado->tipo->label() }}</dd>

                                <dt>Beneficiario</dt>
                                <dd class="featured">{{ $certificado->beneficiario_nombre }}</dd>

                                <dt>Revocado el</dt>
                                <dd>{{ $certificado->revocado_at?->format('d/m/Y H:i') }}</dd>

                                @if ($certificado->motivo_revocacion)
                                    <dt>Motivo</dt>
                                    <dd>{{ $certificado->motivo_revocacion }}</dd>
                                @endif
                            </dl>
                        </div>
                        <div class="hash-bar">
                            <span>Hash SHA-256</span>
                            <code>{{ substr($certificado->hash_verificacion, 0, 32) }}…</code>
                        </div>
                    </div>
                </div>

            @else
                {{-- ============ VÁLIDO (horizontal) ============ --}}
                <div class="grid-horizontal">
                    {{-- IZQUIERDA: status + código + ISOs --}}
                    <div class="left-pane">
                        <div class="status ok">
                            <div class="status-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 12.75 11.25 15 15 9.75"/>
                                    <circle cx="12" cy="12" r="10"/>
                                </svg>
                            </div>
                            <div class="status-text">
                                <div class="status-label">Auténtico</div>
                                <div class="status-title">Certificado válido</div>
                                <div class="status-sub">Emitido por RNFC Consultor de Obras.</div>
                            </div>
                        </div>
                        <div class="codigo-wrap">
                            <div class="codigo-label">Código del certificado</div>
                            <div class="codigo-valor">{{ $certificado->codigo }}</div>
                        </div>
                        <div class="isos-block">
                            <div class="isos-label">Empresa Certificada</div>
                            <div class="isos-grid">
                                <img src="/brand/ISO 9001.png"  alt="ISO 9001:2015">
                                <img src="/brand/ISO 14001.png" alt="ISO 14001:2015">
                                <img src="/brand/ISO 37001.png" alt="ISO 37001:2025">
                            </div>
                        </div>
                    </div>

                    {{-- DERECHA: detalles --}}
                    <div class="right-pane">
                        <div class="details">
                            <div class="section-title">Beneficiario</div>
                            <dl class="info">
                                <dt>Nombre</dt>
                                <dd class="featured">{{ $certificado->beneficiario_nombre }}</dd>

                                @if ($certificado->beneficiario_documento)
                                    <dt>DNI / Documento</dt>
                                    <dd>{{ $certificado->beneficiario_documento }}</dd>
                                @endif

                                @if ($certificado->beneficiario_profesion)
                                    <dt>Profesión</dt>
                                    <dd>{{ $certificado->beneficiario_profesion }}</dd>
                                @endif
                            </dl>
                        </div>

                        <div class="details">
                            <div class="section-title">Certificado</div>
                            <dl class="info">
                                <dt>Tipo</dt>
                                <dd>{{ $certificado->tipo->titulo() }}</dd>

                                @if ($certificado->cargo)
                                    <dt>Cargo / Actividad</dt>
                                    <dd>{{ $certificado->cargo }}</dd>
                                @endif

                                @if ($certificado->obra)
                                    <dt>Obra</dt>
                                    <dd>{{ $certificado->obra->nombre }}</dd>
                                    @if ($certificado->obra->entidad_contratante)
                                        <dt>Contratante</dt>
                                        <dd>{{ $certificado->obra->entidad_contratante }}</dd>
                                    @endif
                                @endif

                                @if ($certificado->fecha_inicio && $certificado->fecha_fin)
                                    <dt>Período</dt>
                                    <dd>{{ $certificado->fecha_inicio->format('d/m/Y') }} — {{ $certificado->fecha_fin->format('d/m/Y') }}</dd>
                                @endif

                                <dt>Emisión</dt>
                                <dd>{{ $certificado->fecha_emision?->format('d/m/Y') }}</dd>

                                <dt>Emitido por</dt>
                                <dd>{{ $certificado->emisor_nombre }}</dd>
                            </dl>
                        </div>

                        <div class="hash-bar">
                            <span>Hash SHA-256 de integridad</span>
                            <code>{{ substr($certificado->hash_verificacion, 0, 32) }}…</code>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </main>

    <footer class="bottom">
        Plataforma oficial de verificación · © {{ date('Y') }}
        <a href="/">RNFC Consultor de Obras</a> · RUC 10421559029
    </footer>
</body>
</html>
