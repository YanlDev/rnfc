<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    <title>Verificar certificado · RNFC Consultor de Obras</title>
    <meta name="description" content="Verifica la autenticidad de un certificado emitido por RNFC Consultor de Obras ingresando el código que figura en el documento.">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Barlow:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --azul: #0b2545;
            --azul-2: #145694;
            --amarillo: #ffd21c;
            --rojo: #c1272d;
            --texto: #1f2937;
            --texto-2: #6b7280;
            --borde: #e5e7eb;
            --bg: #f8fafc;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            min-height: 100vh;
            color: var(--texto);
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
            color: var(--texto-2);
            text-decoration: none;
            font-weight: 500;
        }
        header.top .volver:hover { color: var(--azul-2); }

        /* === MAIN === */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
        }
        .card {
            background: white;
            border: 1px solid var(--borde);
            border-radius: 14px;
            max-width: 560px;
            width: 100%;
            padding: 48px 40px;
            box-shadow: 0 20px 60px -20px rgba(11,37,69,0.15);
        }
        .isos-row {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 28px;
            padding-bottom: 22px;
            border-bottom: 1px solid var(--borde);
        }
        .isos-row img {
            height: 56px;
            width: 56px;
            object-fit: contain;
        }
        .isos-row .isos-label {
            margin-left: auto;
            text-align: right;
            line-height: 1.2;
        }
        .isos-row .isos-label small {
            display: block;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: var(--azul-2);
        }
        .isos-row .isos-label span {
            font-family: 'Barlow', sans-serif;
            font-size: 13px;
            font-weight: 700;
            color: var(--azul);
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        h1 {
            font-family: 'Barlow', sans-serif;
            font-size: 30px;
            font-weight: 700;
            color: var(--azul);
            letter-spacing: -0.015em;
            margin-bottom: 8px;
        }
        .subtitle {
            font-size: 15px;
            color: var(--texto-2);
            line-height: 1.5;
            margin-bottom: 28px;
        }

        form { display: flex; flex-direction: column; gap: 14px; }
        label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--texto-2);
        }
        .input-row { position: relative; }
        input[type="text"] {
            width: 100%;
            border: 1px solid var(--borde);
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 16px;
            font-family: 'Courier New', monospace;
            letter-spacing: 1.5px;
            color: var(--texto);
            background: white;
            text-transform: uppercase;
            transition: border-color .15s, box-shadow .15s;
        }
        input[type="text"]::placeholder {
            color: #cbd5e1;
            letter-spacing: 1.5px;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: var(--azul-2);
            box-shadow: 0 0 0 4px rgba(20,86,148,0.12);
        }
        button {
            border: none;
            border-radius: 10px;
            padding: 14px 20px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            cursor: pointer;
            background: var(--azul);
            color: white;
            transition: background .15s, transform .15s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        button:hover { background: var(--azul-2); transform: translateY(-1px); }
        button svg { width: 16px; height: 16px; }

        .error {
            border-left: 3px solid var(--rojo);
            background: #fef2f2;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 13px;
            color: #991b1b;
        }

        .help {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--borde);
            font-size: 13px;
            color: var(--texto-2);
            line-height: 1.6;
        }
        .help strong { color: var(--texto); font-weight: 600; }

        /* === FOOTER === */
        footer.bottom {
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: var(--texto-2);
            border-top: 1px solid var(--borde);
            background: white;
        }
        footer.bottom a { color: var(--azul-2); text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>
    <header class="top">
        <div class="inner">
            <a href="/" class="brand" aria-label="RNFC Consultor de Obras">
                <img src="/brand/rnfc-logo.png" alt="RNFC Consultor de Obras">
            </a>
            <a href="/" class="volver">← Volver al inicio</a>
        </div>
    </header>

    <main>
        <div class="card">
            <div class="isos-row">
                <img src="/brand/ISO 9001.png"  alt="ISO 9001">
                <img src="/brand/ISO 14001.png" alt="ISO 14001">
                <img src="/brand/ISO 37001.png" alt="ISO 37001">
                <div class="isos-label">
                    <small>Empresa</small>
                    <span>Certificada</span>
                </div>
            </div>

            <h1>Verificar certificado</h1>
            <p class="subtitle">
                Ingresa el código del certificado que figura en el documento o escanea el código QR
                para confirmar su autenticidad.
            </p>

            <form method="POST" action="{{ route('verificar.buscar') }}">
                @csrf
                <label for="codigo">Código del certificado</label>
                <div class="input-row">
                    <input
                        type="text"
                        id="codigo"
                        name="codigo"
                        placeholder="RNFC-2026-ABC123"
                        value="{{ $codigo_ingresado ?? '' }}"
                        required
                        autocomplete="off"
                        spellcheck="false"
                        autofocus
                    >
                </div>

                @if (! empty($error))
                    <div class="error">{{ $error }}</div>
                @endif

                <button type="submit">
                    Verificar
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                    </svg>
                </button>
            </form>

            <div class="help">
                <strong>Formato del código:</strong> <code>RNFC-AAAA-XXXXXX</code> donde
                <code>AAAA</code> es el año y <code>XXXXXX</code> es un identificador único
                de 6 caracteres alfanuméricos.
            </div>
        </div>
    </main>

    <footer class="bottom">
        © {{ date('Y') }} RNFC Consultor de Obras · RUC 10421559029 ·
        <a href="/">rnfcconsultoria.com</a>
    </footer>
</body>
</html>
