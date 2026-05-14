<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#071426">

    @php
        $tituloSeo = 'RNFC — Supervisión de Obras, Control Técnico y Gestión Documental Inteligente';
        $descripcionSeo = 'RNFC integra supervisión técnica, control de calidad, gestión documental y seguimiento digital para proyectos públicos y privados en todo el Perú. ISO 9001, 14001 y 37001.';
        $urlActual = url('/');
        $imagenSeo = asset('brand/rnfc-logo.png');
    @endphp

    <title>{{ $tituloSeo }}</title>
    <meta name="description" content="{{ $descripcionSeo }}">
    <link rel="canonical" href="{{ $urlActual }}">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_PE">
    <meta property="og:site_name" content="RNFC Consultor de Obras">
    <meta property="og:title" content="{{ $tituloSeo }}">
    <meta property="og:description" content="{{ $descripcionSeo }}">
    <meta property="og:url" content="{{ $urlActual }}">
    <meta property="og:image" content="{{ $imagenSeo }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $tituloSeo }}">
    <meta name="twitter:description" content="{{ $descripcionSeo }}">
    <meta name="twitter:image" content="{{ $imagenSeo }}">

    <script type="application/ld+json">
    @verbatim
    {
      "@context": "https://schema.org",
      "@type": ["Organization", "LocalBusiness"],
      "name": "RNFC Consultor de Obras",
    @endverbatim
      "url": "{{ $urlActual }}",
      "logo": "{{ $imagenSeo }}",
      "image": "{{ $imagenSeo }}",
      "description": "{{ $descripcionSeo }}",
    @verbatim
      "founder": {
        "@type": "Person",
        "name": "Roger Neptali Flores Coaquira",
        "jobTitle": "Ingeniero Consultor de Obras"
      },
      "address": {
        "@type": "PostalAddress",
        "addressCountry": "PE",
        "addressLocality": "Juliaca",
        "addressRegion": "Puno",
        "streetAddress": "Jr. Jauregui 1235"
      },
      "email": "contacto@rnfcconsultoria.com",
      "areaServed": "PE",
      "hasCredential": [
        {"@type": "EducationalOccupationalCredential", "name": "ISO 9001:2015"},
        {"@type": "EducationalOccupationalCredential", "name": "ISO 14001:2015"},
        {"@type": "EducationalOccupationalCredential", "name": "ISO 37001:2025"}
      ]
    }
    @endverbatim
    </script>

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        :root {
            --c-bg: #071426;
            --c-bg-2: #0a1a30;
            --c-panel: #0f1f38;
            --c-panel-2: #111827;
            --c-border: rgba(148, 175, 220, 0.12);
            --c-border-strong: rgba(148, 175, 220, 0.22);
            --c-blue: #0F4C81;
            --c-blue-bright: #2563EB;
            --c-cyan: #38bdf8;
            --c-gold: #D4AF37;
            --c-green: #22C55E;
            --c-orange: #F97316;
            --c-text: #e6ecf5;
            --c-text-dim: #94a3b8;
        }

        html, body {
            font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--c-bg);
            color: var(--c-text);
            overflow-x: hidden;
        }
        body {
            font-feature-settings: 'cv02','cv03','cv04','cv11';
            letter-spacing: -0.005em;
            -webkit-font-smoothing: antialiased;
            max-width: 100vw;
        }
        h1,h2,h3,h4,.font-display { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; letter-spacing: -0.02em; }
        .display-tight { letter-spacing: -0.02em; line-height: 1.08; overflow-wrap: anywhere; }

        /* ===== fondos técnicos ===== */
        .bg-blueprint {
            background-color: var(--c-bg);
            background-image:
                linear-gradient(rgba(56,189,248,0.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(56,189,248,0.045) 1px, transparent 1px);
            background-size: 56px 56px;
        }
        .bg-blueprint-fine {
            background-image:
                linear-gradient(rgba(56,189,248,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(56,189,248,0.05) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        .mask-radial {
            mask-image: radial-gradient(ellipse 80% 60% at 50% 40%, black 30%, transparent 80%);
            -webkit-mask-image: radial-gradient(ellipse 80% 60% at 50% 40%, black 30%, transparent 80%);
        }
        .glow-blue {
            background: radial-gradient(closest-side, rgba(37,99,235,0.18), transparent 70%);
            filter: blur(50px);
        }
        .glow-gold {
            background: radial-gradient(closest-side, rgba(212,175,55,0.10), transparent 70%);
            filter: blur(50px);
        }

        /* ===== glass ===== */
        .glass {
            background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.015));
            border: 1px solid var(--c-border);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        .glass-strong {
            background: linear-gradient(180deg, rgba(15,76,129,0.18), rgba(7,20,38,0.55));
            border: 1px solid var(--c-border-strong);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        /* ===== navbar ===== */
        .nav-shell {
            position: fixed; top: 0; left: 0; right: 0; z-index: 50;
            transition: background-color .35s ease, border-color .35s ease, backdrop-filter .35s ease;
            background-color: transparent;
            border-bottom: 1px solid transparent;
        }
        .nav-shell.scrolled {
            background-color: rgba(7,20,38,0.78);
            border-bottom-color: var(--c-border);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }
        .nav-link { color: rgba(230,236,245,0.78); }
        .nav-link:hover { color: #fff; }

        /* ===== botones ===== */
        .btn-primary {
            background: #0F4C81;
            color: #fff;
            box-shadow: 0 8px 24px -12px rgba(15,76,129,0.55), inset 0 1px 0 rgba(255,255,255,0.08);
            transition: transform .25s ease, box-shadow .25s ease, background-color .25s ease;
        }
        .btn-primary:hover { transform: translateY(-2px); background: #145694; box-shadow: 0 14px 32px -14px rgba(15,76,129,0.7), inset 0 1px 0 rgba(255,255,255,0.12); }
        .btn-ghost {
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--c-border-strong);
            color: #fff;
            backdrop-filter: blur(8px);
            transition: background-color .25s ease, border-color .25s ease, transform .25s ease;
        }
        .btn-ghost:hover { background: rgba(255,255,255,0.08); border-color: rgba(148,175,220,0.45); transform: translateY(-2px); }
        .btn-gold {
            background: #ffffff;
            color: #0a1a30;
            box-shadow: 0 8px 22px -12px rgba(255,255,255,0.25), inset 0 1px 0 rgba(255,255,255,0.6);
            transition: transform .25s ease, background-color .25s ease;
        }
        .btn-gold:hover { transform: translateY(-2px); background: #e9eef7; }

        /* ===== reveal ===== */
        .reveal { opacity: 0; transform: translateY(28px); transition: opacity .8s cubic-bezier(.2,.7,.2,1), transform .8s cubic-bezier(.2,.7,.2,1); }
        .reveal.in { opacity: 1; transform: none; }
        .reveal-d1 { transition-delay: .08s; }
        .reveal-d2 { transition-delay: .16s; }
        .reveal-d3 { transition-delay: .24s; }
        .reveal-d4 { transition-delay: .32s; }
        .reveal-d5 { transition-delay: .40s; }

        /* ===== hero fades ===== */
        .hero-fade > * { opacity: 0; transform: translateY(20px); animation: heroIn .9s cubic-bezier(.2,.7,.2,1) forwards; }
        .hero-fade > *:nth-child(1){animation-delay:.10s}
        .hero-fade > *:nth-child(2){animation-delay:.22s}
        .hero-fade > *:nth-child(3){animation-delay:.34s}
        .hero-fade > *:nth-child(4){animation-delay:.46s}
        .hero-fade > *:nth-child(5){animation-delay:.58s}
        @keyframes heroIn { to { opacity:1; transform:none; } }

        /* ===== seccion titulo (sobrio, sin pill) ===== */
        .eyebrow {
            display: inline-flex; align-items: center; gap: .75rem;
            font-size: .7rem; font-weight: 700; letter-spacing: .28em; text-transform: uppercase;
            color: rgba(230, 236, 245, 0.55);
        }
        .eyebrow::before {
            content: ''; width: 28px; height: 1px;
            background: rgba(230, 236, 245, 0.35);
        }
        .eyebrow-center { justify-content: center; }
        .eyebrow-center::after {
            content: ''; width: 28px; height: 1px;
            background: rgba(230, 236, 245, 0.35);
        }

        /* ===== service card ===== */
        .svc-card {
            position: relative; overflow: hidden;
            transition: transform .4s cubic-bezier(.2,.7,.2,1), border-color .4s ease, box-shadow .4s ease;
        }
        .svc-card::before {
            content: ''; position: absolute; inset: -1px; border-radius: inherit; padding: 1px;
            background: linear-gradient(135deg, rgba(37,99,235,.6), rgba(212,175,55,.4), transparent 60%);
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor; mask-composite: exclude;
            opacity: 0; transition: opacity .4s ease;
        }
        .svc-card:hover { transform: translateY(-6px); box-shadow: 0 30px 60px -30px rgba(37,99,235,0.35); }
        .svc-card:hover::before { opacity: 1; }
        .svc-icon {
            width: 52px; height: 52px; border-radius: 14px;
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, rgba(37,99,235,0.22), rgba(15,76,129,0.18));
            border: 1px solid rgba(37,99,235,0.35);
            box-shadow: 0 0 24px rgba(37,99,235,0.25), inset 0 1px 0 rgba(255,255,255,0.08);
            color: #93c5fd;
        }

        /* ===== mockup window ===== */
        .mock-window {
            background: linear-gradient(180deg, #0d1d36 0%, #07142a 100%);
            border: 1px solid var(--c-border-strong);
            border-radius: 16px;
            box-shadow: 0 50px 100px -30px rgba(0,0,0,.7), 0 0 0 1px rgba(255,255,255,.02);
            max-width: 100%;
            overflow: hidden;
        }
        .mock-header {
            display: flex; align-items: center; gap: .4rem;
            padding: .6rem .9rem; border-bottom: 1px solid var(--c-border);
        }
        .mock-dot { width: 10px; height: 10px; border-radius: 999px; }

        /* ===== float cards ===== */
        @keyframes floaty { 0%{transform:translateY(0)} 100%{transform:translateY(-12px)} }
        .floaty { animation: floaty 5s ease-in-out infinite alternate; }
        .floaty-slow { animation: floaty 7s ease-in-out infinite alternate; }

        /* ===== iso card ===== */
        .iso-premium {
            position: relative; overflow: hidden;
            transition: transform .4s ease;
        }
        .iso-premium:hover { transform: translateY(-6px); }
        .iso-premium::after {
            content: ''; position: absolute; inset: -40%;
            background: radial-gradient(closest-side, var(--iso-glow, rgba(37,99,235,.35)), transparent 70%);
            filter: blur(40px);
            opacity: 0; transition: opacity .5s ease;
            pointer-events: none;
        }
        .iso-premium:hover::after { opacity: .8; }

        /* ===== differentiator check ===== */
        .check-pill {
            width: 28px; height: 28px; flex: none;
            border-radius: 6px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(148,175,220,.18);
            display: inline-flex; align-items: center; justify-content: center;
            color: rgba(230,236,245,0.75);
        }

        /* ===== gallery hover ===== */
        .gal-item { transition: transform .5s ease; }
        .gal-item:hover { transform: scale(1.02); }
        .gal-item img { transition: transform .8s cubic-bezier(.2,.7,.2,1), filter .5s ease; }
        .gal-item:hover img { transform: scale(1.08); filter: saturate(1.1) brightness(1.05); }

        /* ===== particle dots ===== */
        .particles {
            position: absolute; inset: 0; overflow: hidden; pointer-events: none;
        }
        .particles::before, .particles::after {
            content: ''; position: absolute; width: 280px; height: 280px; border-radius: 999px;
            background: radial-gradient(closest-side, rgba(37,99,235,.25), transparent 70%);
            filter: blur(40px);
        }
        .particles::before { top: -80px; left: -80px; }
        .particles::after { bottom: -80px; right: -80px; background: radial-gradient(closest-side, rgba(212,175,55,.18), transparent 70%); }

        /* ===== map pulse ===== */
        @keyframes pulseRing { 0%{transform:scale(.6); opacity:.9} 100%{transform:scale(2.2); opacity:0} }
        .map-pulse::after {
            content:''; position:absolute; inset:-6px; border-radius:999px;
            border: 2px solid rgba(37,99,235,.7);
            animation: pulseRing 2.2s ease-out infinite;
        }

        /* ===== utilities ===== */
        details > summary { list-style: none; cursor: pointer; }
        details > summary::-webkit-details-marker { display: none; }
        ::selection { background: rgba(37,99,235,.35); color: #fff; }

        @media (prefers-reduced-motion: reduce) {
            .reveal, .hero-fade > *, .floaty, .floaty-slow, .map-pulse::after { animation: none !important; transition: none !important; opacity: 1 !important; transform: none !important; }
        }
    </style>
</head>
<body class="bg-blueprint text-[var(--c-text)] antialiased">

    {{-- ================================================================
         NAVBAR
    ================================================================ --}}
    <header id="nav" class="nav-shell">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 md:px-8">
            <a href="#inicio" class="flex items-center gap-3">
                <img src="/brand/rnfc-logo.png" alt="RNFC" class="h-10 w-auto drop-shadow-[0_4px_18px_rgba(37,99,235,.45)] md:h-12">
                <span class="sr-only">RNFC Consultor de Obras</span>
            </a>
            <nav class="hidden items-center gap-7 lg:flex">
                <a href="#servicios" class="nav-link text-sm font-semibold transition-colors">Servicios</a>
                <a href="#plataforma" class="nav-link text-sm font-semibold transition-colors">Plataforma</a>
                <a href="#experiencia" class="nav-link text-sm font-semibold transition-colors">Experiencia</a>
                <a href="#certificaciones" class="nav-link text-sm font-semibold transition-colors">ISO</a>
                <a href="{{ route('verificar.form') }}" class="nav-link text-sm font-semibold transition-colors">Verificar</a>
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="btn-ghost rounded-lg px-3 py-2 text-xs font-semibold md:px-4 md:text-sm">Iniciar sesión</a>
                <a href="#contacto" class="btn-primary hidden rounded-lg px-4 py-2 text-sm font-semibold md:inline-block">Contactar</a>
                <button id="navToggle" type="button" aria-label="Menú" class="btn-ghost inline-flex size-9 items-center justify-center rounded-lg lg:hidden">
                    <svg id="navIconOpen" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                    <svg id="navIconClose" class="hidden size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>
        </div>

        {{-- panel mobile --}}
        <div id="navMobile" class="hidden border-t border-white/5 bg-[#04101e]/95 backdrop-blur-xl lg:hidden">
            <nav class="mx-auto flex max-w-7xl flex-col px-4 py-3 text-sm font-semibold">
                <a href="#servicios" class="border-b border-white/5 py-3 text-white/80 hover:text-white">Servicios</a>
                <a href="#plataforma" class="border-b border-white/5 py-3 text-white/80 hover:text-white">Plataforma</a>
                <a href="#experiencia" class="border-b border-white/5 py-3 text-white/80 hover:text-white">Experiencia</a>
                <a href="#certificaciones" class="border-b border-white/5 py-3 text-white/80 hover:text-white">ISO</a>
                <a href="{{ route('verificar.form') }}" class="border-b border-white/5 py-3 text-white/80 hover:text-white">Verificar certificado</a>
                <a href="#contacto" class="py-3 text-white/80 hover:text-white">Contactar</a>
            </nav>
        </div>
    </header>

    {{-- ================================================================
         HERO
    ================================================================ --}}
    <section id="inicio" class="relative isolate overflow-hidden pt-24 pb-16 md:pt-40 md:pb-32">
        {{-- fondo: imagen obra + overlay azul + grid técnica --}}
        <div class="absolute inset-0 -z-20">
            <img src="/brand/supervision.png" alt="" class="h-full w-full object-cover opacity-20 md:opacity-30">
        </div>
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-[#071426]/97 via-[#0a1a30]/95 to-[#071426] md:from-[#071426]/95 md:via-[#0a1a30]/92 md:to-[#071426]/98"></div>
        <div class="bg-blueprint-fine mask-radial absolute inset-0 -z-10 opacity-60"></div>
        <div class="glow-blue absolute -top-20 -left-20 -z-10 h-[500px] w-[500px]"></div>
        <div class="glow-gold absolute -bottom-20 -right-20 -z-10 h-[450px] w-[450px]"></div>

        <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 md:px-8 md:gap-12 lg:grid-cols-[1.05fr_1fr]">

            {{-- LADO IZQUIERDO --}}
            <div class="hero-fade">
                <div class="eyebrow">RNFC · Consultor de obras</div>

                <h1 class="display-tight mt-5 text-[1.4rem] font-extrabold text-white sm:text-[1.7rem] md:text-[2rem] lg:text-[2.75rem]" style="hyphens: auto; -webkit-hyphens: auto;" lang="es">
                    No solo supervisamos obras —
                    <span class="text-white/70">también las formulamos, planificamos y gestionamos</span>
                    de extremo a extremo.
                </h1>

                <p class="mt-5 max-w-xl text-sm leading-relaxed text-[var(--c-text-dim)] md:mt-7 md:text-lg">
                    RNFC integra formulación de expedientes técnicos, planificación,
                    supervisión, control de calidad y gestión documental digital para
                    proyectos públicos y privados en todo el Perú. Un solo equipo, un solo
                    estándar, del estudio a la entrega.
                </p>
            </div>

            {{-- LADO DERECHO: mockup dashboard --}}
            <div class="relative">
                <div class="glow-blue absolute -inset-10 -z-10"></div>

                <div class="mock-window relative">
                    <div class="mock-header">
                        <span class="mock-dot bg-red-400/70"></span>
                        <span class="mock-dot bg-amber-300/70"></span>
                        <span class="mock-dot bg-emerald-400/70"></span>
                        <span class="ml-3 text-[11px] font-semibold tracking-wider text-[var(--c-text-dim)]">rnfc.app / panel · supervisión</span>
                        <span class="ml-auto inline-flex items-center gap-1 rounded-full border border-emerald-400/30 bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold text-emerald-300">
                            <span class="size-1.5 rounded-full bg-emerald-400"></span> En obra
                        </span>
                    </div>

                    <div class="grid gap-2.5 p-3 md:gap-3 md:p-4">
                        {{-- fila kpis --}}
                        <div class="grid grid-cols-3 gap-2 md:gap-3">
                            @foreach ([
                                ['lbl' => 'Avance', 'lblFull' => 'Avance físico', 'val' => '78%', 'sub' => '+4.2% sem', 'col' => 'from-blue-500/30 to-blue-500/5', 'icon' => 'M3 17 9 11l4 4 8-8M14 7h7v7'],
                                ['lbl' => 'Conform.', 'lblFull' => 'Conformidades', 'val' => '142', 'sub' => '12 abiertas', 'col' => 'from-emerald-500/25 to-emerald-500/5', 'icon' => 'M9 12l2 2 4-4M12 22a10 10 0 1 1 0-20 10 10 0 0 1 0 20Z'],
                                ['lbl' => 'Docs.', 'lblFull' => 'Documentos', 'val' => '1,284', 'sub' => '38 hoy', 'col' => 'from-amber-400/25 to-amber-400/5', 'icon' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6'],
                            ] as $k)
                            <div class="rounded-xl border border-white/10 bg-gradient-to-br {{ $k['col'] }} p-2.5 md:p-3">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-[9px] font-semibold uppercase tracking-wider text-white/60 md:text-[10px]">
                                        <span class="md:hidden">{{ $k['lbl'] }}</span>
                                        <span class="hidden md:inline">{{ $k['lblFull'] }}</span>
                                    </span>
                                    <svg class="size-3 text-white/70 md:size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $k['icon'] }}"/></svg>
                                </div>
                                <div class="mt-1 font-display text-lg font-extrabold text-white md:text-2xl">{{ $k['val'] }}</div>
                                <div class="text-[9px] font-semibold text-white/55 md:text-[10px]">{{ $k['sub'] }}</div>
                            </div>
                            @endforeach
                        </div>

                        {{-- gráfico fake --}}
                        <div class="rounded-xl border border-white/10 bg-white/[0.02] p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-white/55">Curva S · Avance vs programado</div>
                                    <div class="mt-1 font-display text-lg font-extrabold text-white">Carretera PE-3S · Tramo 4</div>
                                </div>
                                <div class="flex gap-2 text-[10px] font-semibold text-white/55">
                                    <span class="inline-flex items-center gap-1"><span class="size-2 rounded-full bg-[#2563EB]"></span>Real</span>
                                    <span class="inline-flex items-center gap-1"><span class="size-2 rounded-full bg-[#D4AF37]"></span>Prog.</span>
                                </div>
                            </div>
                            <svg viewBox="0 0 320 110" class="mt-3 h-24 w-full">
                                <defs>
                                    <linearGradient id="gArea" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#2563EB" stop-opacity=".45"/>
                                        <stop offset="100%" stop-color="#2563EB" stop-opacity="0"/>
                                    </linearGradient>
                                </defs>
                                {{-- grid --}}
                                <g stroke="rgba(148,175,220,.10)">
                                    <line x1="0" y1="25" x2="320" y2="25"/>
                                    <line x1="0" y1="55" x2="320" y2="55"/>
                                    <line x1="0" y1="85" x2="320" y2="85"/>
                                </g>
                                <path d="M0,95 C50,90 90,80 130,65 C170,52 210,45 260,28 C290,18 310,12 320,10 L320,110 L0,110 Z" fill="url(#gArea)"/>
                                <path d="M0,95 C50,90 90,80 130,65 C170,52 210,45 260,28 C290,18 310,12 320,10" fill="none" stroke="#60a5fa" stroke-width="2.2"/>
                                <path d="M0,100 C50,95 100,88 140,75 C180,62 220,52 260,38 C290,28 310,22 320,18" fill="none" stroke="#D4AF37" stroke-width="1.8" stroke-dasharray="4 4"/>
                            </svg>
                        </div>

                        {{-- lista documentos --}}
                        <div class="rounded-xl border border-white/10 bg-white/[0.02]">
                            <div class="flex items-center justify-between border-b border-white/10 px-4 py-2.5">
                                <span class="text-[11px] font-semibold uppercase tracking-wider text-white/55">Cuaderno de obra · últimos asientos</span>
                                <span class="text-[10px] font-semibold text-emerald-300">● firmado</span>
                            </div>
                            <ul class="divide-y divide-white/5 text-xs">
                                @foreach ([
                                    ['n' => 'Asiento N° 0247', 'd' => 'Verificación de subrasante km 12+400', 't' => '13:42'],
                                    ['n' => 'Asiento N° 0246', 'd' => 'Conformidad ensayo proctor — calicata 18', 't' => '11:08'],
                                    ['n' => 'Asiento N° 0245', 'd' => 'NC: traslapes acero menor a 60 cm — corrige', 't' => '09:30', 'nc' => true],
                                ] as $a)
                                    <li class="flex items-center gap-3 px-4 py-2.5">
                                        <span class="inline-flex size-7 items-center justify-center rounded-md border border-white/10 bg-white/[0.04] text-[10px] font-bold text-white/80">PDF</span>
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-[12px] font-bold text-white">{{ $a['n'] }}</div>
                                            <div class="truncate text-[11px] text-white/55">{{ $a['d'] }}</div>
                                        </div>
                                        @if (!empty($a['nc']))
                                            <span class="rounded-full border border-orange-400/40 bg-orange-500/10 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-orange-300">No conformidad</span>
                                        @else
                                            <span class="rounded-full border border-emerald-400/30 bg-emerald-500/10 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider text-emerald-300">Conforme</span>
                                        @endif
                                        <span class="text-[10px] font-semibold text-white/45">{{ $a['t'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- tarjeta flotante calidad --}}
                <div class="floaty glass absolute -left-6 -bottom-6 hidden w-60 rounded-2xl p-4 md:block">
                    <div class="flex items-center gap-3">
                        <div class="inline-flex size-10 items-center justify-center rounded-xl border border-emerald-400/40 bg-emerald-500/15 text-emerald-300">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold uppercase tracking-wider text-emerald-200/80">QA/QC</div>
                            <div class="font-display text-base font-extrabold text-white">98.2%</div>
                            <div class="text-[10px] text-white/55">conformidad técnica</div>
                        </div>
                    </div>
                </div>

                {{-- tarjeta flotante BIM --}}
                <div class="floaty-slow glass absolute -right-4 -top-4 hidden w-52 rounded-2xl p-3 md:block">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-[var(--c-text-dim)]">Modelo BIM</span>
                        <span class="size-2 rounded-full bg-sky-400 shadow-[0_0_8px_#38bdf8]"></span>
                    </div>
                    <svg viewBox="0 0 100 60" class="mt-1 h-16 w-full text-sky-300/80">
                        <path d="M10,50 L50,15 L90,50 Z" fill="none" stroke="currentColor" stroke-width="1.2"/>
                        <path d="M10,50 L50,35 L90,50" fill="none" stroke="currentColor" stroke-width="1" stroke-dasharray="3 3"/>
                        <path d="M50,15 L50,35" fill="none" stroke="currentColor" stroke-width="1" stroke-dasharray="2 3"/>
                        <circle cx="10" cy="50" r="2" fill="#60a5fa"/><circle cx="90" cy="50" r="2" fill="#60a5fa"/><circle cx="50" cy="15" r="2" fill="#D4AF37"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================
         BARRA DE CONFIANZA
    ================================================================ --}}
    <section class="border-y border-white/5 bg-[#050f1f]/80">
        <div class="mx-auto max-w-7xl px-4 py-6 md:px-8 md:py-8">
            <div class="grid grid-cols-3 items-center gap-4 text-center sm:grid-cols-4 md:gap-6 lg:grid-cols-7">
                @foreach ([
                    ['ISO 9001', 'Calidad'],
                    ['ISO 14001', 'Ambiental'],
                    ['ISO 37001', 'Antisoborno'],
                    ['QA/QC', 'Control técnico'],
                    ['SSOMA', 'Seguridad y salud'],
                    ['BIM', 'Modelado digital'],
                    ['Ley 32069', 'Contrataciones del Estado'],
                ] as $i => $t)
                    <div class="reveal reveal-d{{ min($i+1,5) }} group flex flex-col items-center gap-1.5 border-white/5 px-2 lg:border-l">
                        <span class="font-display text-base font-extrabold tracking-tight text-white transition-colors group-hover:text-[#60a5fa]">{{ $t[0] }}</span>
                        <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-[var(--c-text-dim)]">{{ $t[1] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================================================================
         QUÉ HACEMOS
    ================================================================ --}}
    <section id="servicios" class="relative overflow-hidden py-16 md:py-32">
        <div class="particles"></div>

        <div class="relative mx-auto max-w-7xl px-4 md:px-8">
            <div class="mx-auto max-w-2xl text-center reveal">
                <div class="eyebrow eyebrow-center">Qué hacemos</div>
                <h2 class="mt-5 font-display text-2xl font-extrabold tracking-tight text-white sm:text-3xl md:text-5xl">
                    Servicios técnicos integrados<br>
                    <span class="text-white/55">en una sola operación.</span>
                </h2>
                <p class="mt-5 text-base text-[var(--c-text-dim)] md:text-lg">
                    Supervisión, control documental, calidad, seguridad y plataforma digital — operando bajo procesos certificados.
                </p>
            </div>

            <div class="mt-16 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                @php
                    $svcs = [
                        [
                            'titulo' => 'Supervisión de obras',
                            'desc'   => 'Control técnico, avance físico, valorizaciones, inspecciones y conformidades de obra.',
                            'items'  => ['Control técnico', 'Avance físico', 'Valorizaciones', 'Inspecciones'],
                            'icon'   => '<path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-7h6v7M9 11h.01M15 11h.01"/>',
                        ],
                        [
                            'titulo' => 'Gestión documental',
                            'desc'   => 'Expedientes digitales, trazabilidad, control de versiones y firmas auditables.',
                            'items'  => ['Expedientes digitales', 'Trazabilidad', 'Control documental', 'Versionado'],
                            'icon'   => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M9 13h6M9 17h6M9 9h2"/>',
                        ],
                        [
                            'titulo' => 'QA/QC y SSOMA',
                            'desc'   => 'Gestión de calidad, seguridad, medio ambiente y resolución de no conformidades.',
                            'items'  => ['Calidad', 'Seguridad', 'Medio ambiente', 'No conformidades'],
                            'icon'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10ZM9 12l2 2 4-4"/>',
                        ],
                        [
                            'titulo' => 'Plataforma digital',
                            'desc'   => 'Seguimiento en tiempo real, reportes, alertas y control administrativo de obras.',
                            'items'  => ['Tiempo real', 'Reportes', 'Alertas', 'Control admin.'],
                            'icon'   => '<rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 21h8M12 18v3M7 14l3-3 3 3 4-5"/>',
                        ],
                    ];
                @endphp

                @foreach ($svcs as $i => $s)
                    <div class="svc-card reveal reveal-d{{ min($i+1,5) }} glass relative rounded-2xl p-6">
                        <span class="svc-icon">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $s['icon'] !!}</svg>
                        </span>
                        <h3 class="mt-5 font-display text-xl font-extrabold text-white">{{ $s['titulo'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-[var(--c-text-dim)]">{{ $s['desc'] }}</p>
                        <ul class="mt-5 space-y-2 text-[13px] text-white/80">
                            @foreach ($s['items'] as $it)
                                <li class="flex items-center gap-2">
                                    <svg class="size-3.5 text-[#60a5fa]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-11"/></svg>
                                    {{ $it }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================================================================
         PLATAFORMA / SISTEMA DOCUMENTAL
    ================================================================ --}}
    <section id="plataforma" class="relative overflow-hidden bg-gradient-to-b from-[#06101e] via-[#071426] to-[#06101e] py-16 md:py-32">
        <div class="bg-blueprint-fine absolute inset-0 mask-radial opacity-50"></div>
        <div class="glow-blue absolute right-0 top-20 -z-0 h-[420px] w-[420px]"></div>

        <div class="relative mx-auto max-w-7xl px-4 md:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1fr_1.15fr]">
                <div class="reveal">
                    <div class="eyebrow">Plataforma propia</div>
                    <h2 class="mt-5 font-display text-2xl font-extrabold tracking-tight text-white sm:text-3xl md:text-5xl">
                        Control documental y seguimiento técnico
                        <span class="block text-white/55">en una sola plataforma.</span>
                    </h2>
                    <p class="mt-5 text-base leading-relaxed text-[var(--c-text-dim)] md:text-lg">
                        Plataforma interna desarrollada para gestionar obras, expedientes, certificados,
                        equipo de obra, calendario, cuaderno de obra digital y documentación auditable —
                        accesible desde laptop, tablet o campo.
                    </p>

                    <div class="mt-8 grid grid-cols-2 gap-3">
                        @foreach ([
                            ['Obras', 'M3 21h18M5 21V8l7-5 7 5v13'],
                            ['Certificados', 'M9 12l2 2 4-4M12 22a10 10 0 1 1 0-20 10 10 0 0 1 0 20Z'],
                            ['Equipo', 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75'],
                            ['Documentos', 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6'],
                            ['Cuaderno de obra', 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z'],
                            ['Administración', 'M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z'],
                            ['Usuarios', 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8'],
                            ['Calendario', 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z'],
                        ] as $m)
                            <div class="glass flex items-center gap-3 rounded-xl px-3 py-2.5">
                                <span class="inline-flex size-9 items-center justify-center rounded-lg border border-[#2563EB]/30 bg-[#2563EB]/10 text-[#93c5fd]">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $m[1] }}"/></svg>
                                </span>
                                <span class="text-[13px] font-bold text-white">{{ $m[0] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('login') }}" class="btn-primary inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold">
                            Acceder a la plataforma
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                        <a href="{{ route('verificar.form') }}" class="btn-ghost inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold">
                            Verificar certificado
                        </a>
                    </div>
                </div>

                {{-- mockup laptop --}}
                <div class="reveal reveal-d2 relative">
                    <div class="glow-gold absolute -inset-10 -z-10"></div>
                    <div class="mock-window relative">
                        <div class="mock-header">
                            <span class="mock-dot bg-red-400/70"></span>
                            <span class="mock-dot bg-amber-300/70"></span>
                            <span class="mock-dot bg-emerald-400/70"></span>
                            <span class="ml-3 text-[11px] font-semibold tracking-wider text-[var(--c-text-dim)]">rnfc.app / obras / saneamiento-azangaro</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-[170px_1fr]">
                            {{-- sidebar --}}
                            <aside class="hidden border-r border-white/5 p-3 sm:block">
                                <div class="px-2 text-[10px] font-bold uppercase tracking-wider text-white/40">Menú</div>
                                <ul class="mt-2 space-y-1 text-[12px] font-semibold text-white/75">
                                    <li class="rounded-md bg-[#2563EB]/15 px-2 py-1.5 text-white">Obras</li>
                                    <li class="px-2 py-1.5">Cuaderno</li>
                                    <li class="px-2 py-1.5">Documentos</li>
                                    <li class="px-2 py-1.5">Calendario</li>
                                    <li class="px-2 py-1.5">Equipo</li>
                                    <li class="px-2 py-1.5">Certificados</li>
                                </ul>
                            </aside>

                            {{-- contenido --}}
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-white/45">Obra activa</div>
                                        <div class="font-display text-base font-extrabold text-white">Saneamiento — Azángaro</div>
                                    </div>
                                    <span class="rounded-full border border-emerald-400/30 bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold text-emerald-300">En ejecución</span>
                                </div>

                                <div class="mt-3 grid grid-cols-3 gap-2">
                                    <div class="rounded-lg border border-white/10 bg-white/[0.02] p-2.5">
                                        <div class="text-[9px] font-bold uppercase tracking-wider text-white/45">Avance</div>
                                        <div class="font-display text-lg font-extrabold text-white">62%</div>
                                        <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-white/10">
                                            <div class="h-full w-[62%] rounded-full bg-gradient-to-r from-[#2563EB] to-[#60a5fa]"></div>
                                        </div>
                                    </div>
                                    <div class="rounded-lg border border-white/10 bg-white/[0.02] p-2.5">
                                        <div class="text-[9px] font-bold uppercase tracking-wider text-white/45">Valorizado</div>
                                        <div class="font-display text-lg font-extrabold text-white">S/ 4.2M</div>
                                        <div class="text-[10px] text-emerald-300/80">+S/ 280k</div>
                                    </div>
                                    <div class="rounded-lg border border-white/10 bg-white/[0.02] p-2.5">
                                        <div class="text-[9px] font-bold uppercase tracking-wider text-white/45">NC abiertas</div>
                                        <div class="font-display text-lg font-extrabold text-white">3</div>
                                        <div class="text-[10px] text-orange-300/80">2 en revisión</div>
                                    </div>
                                </div>

                                <div class="mt-3 rounded-lg border border-white/10 bg-white/[0.02] p-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-white/45">Carpetas del expediente</span>
                                        <span class="text-[10px] font-semibold text-[#93c5fd]">12 carpetas</span>
                                    </div>
                                    <div class="mt-2 grid grid-cols-3 gap-2 text-[11px] text-white/85">
                                        @foreach (['00 — Bases', '01 — Técnico', '02 — Económico', '03 — Calidad', '04 — SSOMA', '05 — Cierre'] as $c)
                                            <div class="flex items-center gap-1.5 rounded-md border border-white/10 bg-white/[0.02] px-2 py-1.5">
                                                <svg class="size-3.5 text-[#D4AF37]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 3h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                                <span class="truncate">{{ $c }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- tablet flotante --}}
                    <div class="floaty glass absolute -left-8 -bottom-10 hidden w-56 rounded-2xl p-3 lg:block">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-[var(--c-text-dim)]">Cuaderno digital</span>
                            <span class="text-[10px] font-bold text-emerald-300">● firmado</span>
                        </div>
                        <div class="mt-2 space-y-1 text-[11px] text-white/80">
                            <div class="rounded-md border border-white/10 bg-white/[0.03] px-2 py-1.5">N° 0247 · 14:02</div>
                            <div class="rounded-md border border-white/10 bg-white/[0.03] px-2 py-1.5">N° 0246 · 13:18</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================
         EXPERIENCIA
    ================================================================ --}}
    <section id="experiencia" class="relative overflow-hidden py-16 md:py-32">
        <div class="bg-blueprint-fine absolute inset-0 mask-radial opacity-40"></div>

        <div class="relative mx-auto max-w-7xl px-4 md:px-8">
            <div class="mx-auto max-w-3xl text-center reveal">
                <div class="eyebrow eyebrow-center">Experiencia técnica</div>
                <h2 class="mt-5 font-display text-2xl font-extrabold tracking-tight text-white sm:text-3xl md:text-5xl">
                    Experiencia técnica<br>
                    <span class="text-white/55">en obras públicas y privadas.</span>
                </h2>
                <p class="mt-5 text-base text-[var(--c-text-dim)] md:text-lg">
                    Trabajamos en supervisión y consultoría de proyectos de infraestructura junto a
                    entidades públicas y empresas constructoras a nivel nacional.
                </p>
            </div>

            {{-- pilares de experiencia (sin cifras públicas) --}}
            <div class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    [
                        'Obras viales',
                        'Supervisión técnica en carreteras, vías urbanas y obras de arte.',
                        'M3 21h18M5 21V8M19 21V8M9 21v-8h6v8M2 8h20',
                    ],
                    [
                        'Edificaciones',
                        'Edificios institucionales, educativos, salud y vivienda multifamiliar.',
                        'M3 21h18M6 21V5h12v16M9 9h2M13 9h2M9 13h2M13 13h2M9 17h2M13 17h2',
                    ],
                    [
                        'Saneamiento',
                        'Sistemas de agua potable, alcantarillado y plantas de tratamiento.',
                        'M12 2v6M12 8c-3 4-6 7-6 10a6 6 0 0 0 12 0c0-3-3-6-6-10Z',
                    ],
                    [
                        'Electromecánicas',
                        'Obras electromecánicas, instalaciones y proyectos especiales.',
                        'M13 2 4 14h7l-1 8 9-12h-7l1-8Z',
                    ],
                    [
                        'Obras públicas',
                        'Experiencia con Gobiernos Regionales, Municipalidades y Ministerios.',
                        'M3 21h18M5 21V10l7-5 7 5v11M9 21v-6h6v6M9 14h.01M15 14h.01',
                    ],
                    [
                        'Obras privadas',
                        'Supervisión y consultoría para empresas constructoras y desarrolladoras.',
                        'M3 7h18M3 12h18M3 17h18M7 3v18M17 3v18',
                    ],
                ] as $i => $p)
                    <div class="reveal reveal-d{{ min(($i % 5)+1, 5) }} glass rounded-2xl p-6">
                        <span class="inline-flex size-11 items-center justify-center rounded-lg border border-white/10 bg-white/[0.03] text-white/70">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $p[2] }}"/></svg>
                        </span>
                        <div class="mt-4 font-display text-lg font-extrabold text-white">{{ $p[0] }}</div>
                        <p class="mt-1.5 text-sm leading-relaxed text-[var(--c-text-dim)]">{{ $p[1] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- compromiso técnico --}}
            <div class="reveal mt-16 glass rounded-2xl p-6 md:p-10">
                <div class="grid gap-8 md:grid-cols-[1fr_1.4fr]">
                    <div>
                        <h3 class="font-display text-xl font-extrabold text-white md:text-2xl">Estándares de trabajo</h3>
                        <p class="mt-2 text-sm text-[var(--c-text-dim)]">
                            Operamos bajo la normativa aplicable al sector construcción en el Perú
                            y bajo nuestros sistemas de gestión certificados.
                        </p>
                    </div>
                    <ul class="grid gap-3 sm:grid-cols-2">
                        @foreach ([
                            'Ley de Contrataciones del Estado',
                            'Reglamento Nacional de Edificaciones',
                            'ISO 9001 · 14001 · 37001 vigentes',
                            'Procedimientos QA/QC y SSOMA',
                            'Buenas prácticas BIM',
                            'Trazabilidad documental auditable',
                        ] as $e)
                            <li class="flex items-center gap-3 text-sm text-white/85">
                                <span class="inline-flex size-6 items-center justify-center rounded-md border border-white/10 bg-white/[0.03] text-white/65">
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-11"/></svg>
                                </span>
                                {{ $e }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================
         CERTIFICACIONES ISO
    ================================================================ --}}
    <section id="certificaciones" class="relative overflow-hidden bg-gradient-to-b from-[#06101e] via-[#08182d] to-[#06101e] py-16 md:py-32">
        <div class="glow-gold absolute left-1/2 top-10 -z-0 h-[400px] w-[400px] -translate-x-1/2"></div>

        <div class="relative mx-auto max-w-7xl px-4 md:px-8">
            <div class="mx-auto max-w-2xl text-center reveal">
                <div class="eyebrow eyebrow-center">Certificaciones internacionales</div>
                <h2 class="mt-5 font-display text-2xl font-extrabold tracking-tight text-white sm:text-3xl md:text-5xl">
                    Calidad, sostenibilidad<br>
                    <span class="text-white/55">e integridad institucional.</span>
                </h2>
                <p class="mt-5 text-base text-[var(--c-text-dim)] md:text-lg">
                    Operamos bajo sistemas de gestión certificados por organismos acreditados.
                    Procesos auditables, mejora continua y trazabilidad documental.
                </p>
            </div>

            <div class="mt-14 grid gap-6 md:grid-cols-3">
                @php
                    $isos = [
                        ['ISO 9001:2015', 'Sistema de Gestión de la Calidad', 'Procesos auditables, control documental y mejora continua aplicada a cada proyecto.', '/brand/ISO 9001.png', 'rgba(37,99,235,.4)', '#60a5fa', 'Calidad'],
                        ['ISO 14001:2015', 'Sistema de Gestión Ambiental', 'Identificación, control y mitigación de impactos ambientales en obra.', '/brand/ISO 14001.png', 'rgba(34,197,94,.45)', '#4ade80', 'Sostenibilidad'],
                        ['ISO 37001:2025', 'Sistema de Gestión Antisoborno', 'Política de cero soborno, debida diligencia y transparencia con todas las partes.', '/brand/ISO 37001.png', 'rgba(249,115,22,.45)', '#fb923c', 'Integridad'],
                    ];
                @endphp

                @foreach ($isos as $i => $iso)
                    <div class="iso-premium reveal reveal-d{{ $i+1 }} glass-strong relative overflow-hidden rounded-2xl p-7" style="--iso-glow: {{ $iso[4] }}">
                        <div class="relative z-10 flex items-start justify-between gap-3">
                            <div class="size-24 shrink-0 md:size-32">
                                <img src="{{ $iso[3] }}" alt="{{ $iso[0] }}" class="h-full w-full object-contain drop-shadow-[0_10px_30px_rgba(0,0,0,.5)]">
                            </div>
                            <span class="rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider"
                                  style="color: {{ $iso[5] }}; border-color: {{ $iso[4] }}; background: rgba(255,255,255,0.03)">
                                {{ $iso[6] }}
                            </span>
                        </div>
                        <div class="relative z-10 mt-5">
                            <div class="font-display text-2xl font-extrabold text-white">{{ $iso[0] }}</div>
                            <div class="mt-1 text-sm font-bold uppercase tracking-wider" style="color: {{ $iso[5] }}">{{ $iso[1] }}</div>
                            <p class="mt-3 text-sm leading-relaxed text-[var(--c-text-dim)]">{{ $iso[2] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="reveal mt-12 grid gap-4 md:grid-cols-4">
                @foreach (['Calidad', 'Sostenibilidad', 'Transparencia', 'Mejora continua'] as $v)
                    <div class="glass flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold text-white">
                        <svg class="size-4 text-white/55" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-11"/></svg>
                        {{ $v }}
                    </div>
                @endforeach
            </div>

            <p class="mx-auto mt-10 max-w-3xl text-center text-sm text-white/55 reveal">
                Certificados emitidos por <strong class="text-white/80">International Certification Organization (ICO)</strong> —
                verificables en
                <a href="https://icocert.pe" target="_blank" rel="noopener" class="text-white/85 underline underline-offset-4 hover:text-white">icocert.pe</a>.
            </p>
        </div>
    </section>

    {{-- ================================================================
         DIFERENCIADORES
    ================================================================ --}}
    <section id="diferenciadores" class="relative overflow-hidden py-16 md:py-32">
        <div class="bg-blueprint-fine absolute inset-0 mask-radial opacity-40"></div>
        <div class="glow-blue absolute -left-10 top-20 -z-0 h-[400px] w-[400px]"></div>

        <div class="relative mx-auto max-w-7xl px-4 md:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-[1fr_1.4fr]">
                <div class="reveal lg:sticky lg:top-32">
                    <div class="eyebrow">Por qué RNFC</div>
                    <h2 class="mt-5 font-display text-2xl font-extrabold tracking-tight text-white sm:text-3xl md:text-5xl">
                        Una firma con<br>
                        <span class="text-white/55">transformación digital</span><br>
                        y procesos certificados.
                    </h2>
                    <p class="mt-5 text-base text-[var(--c-text-dim)] md:text-lg">
                        Combinamos experiencia técnica con tecnología propia — algo que las consultoras
                        tradicionales del sector aún no ofrecen.
                    </p>
                </div>

                <ul class="grid gap-3 md:grid-cols-2">
                    @foreach ([
                        ['Supervisión técnica especializada', 'Equipo multidisciplinario con experiencia en obras viales, edificación, saneamiento y electromecánicas.'],
                        ['Gestión documental inteligente', 'Plataforma con versionado, búsqueda, firma y trazabilidad por obra.'],
                        ['Plataforma digital propia', 'Sistema desarrollado in-house — no dependemos de software de terceros para el control diario.'],
                        ['Cumplimiento normativo', 'Operamos bajo Ley de Contrataciones del Estado, RNE y reglamentos sectoriales.'],
                        ['Trazabilidad y control', 'Cada documento, asiento y conformidad queda registrado y auditable.'],
                        ['Experiencia en obras públicas', 'Supervisión para gobiernos regionales, municipalidades y ministerios.'],
                        ['Transparencia institucional', 'ISO 37001 — política antisoborno y debida diligencia con todas las partes.'],
                        ['Estándares internacionales ISO', 'Certificación triple ISO 9001, 14001 y 37001 vigente.'],
                    ] as $i => $d)
                        <li class="reveal reveal-d{{ min(($i % 5)+1, 5) }} glass flex items-start gap-4 rounded-2xl p-5">
                            <span class="check-pill mt-0.5">
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-11"/></svg>
                            </span>
                            <div>
                                <div class="font-display text-[15px] font-extrabold text-white">{{ $d[0] }}</div>
                                <div class="mt-1 text-sm text-[var(--c-text-dim)]">{{ $d[1] }}</div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- ================================================================
         GALERÍA DE OBRAS
    ================================================================ --}}
    <section id="galeria" class="relative overflow-hidden bg-gradient-to-b from-[#06101e] via-[#071426] to-[#06101e] py-16 md:py-32">
        <div class="relative mx-auto max-w-7xl px-4 md:px-8">
            <div class="mx-auto max-w-2xl text-center reveal">
                <div class="eyebrow eyebrow-center">Galería de obras</div>
                <h2 class="mt-5 font-display text-2xl font-extrabold tracking-tight text-white sm:text-3xl md:text-5xl">
                    Infraestructura supervisada<br>
                    <span class="text-white/55">en campo y en digital.</span>
                </h2>
            </div>

            @php
                $galeria = $galeria ?? collect();
                // Alturas variables para layout masonry-style
                $alturas = ['h-[420px]', 'h-[200px]', 'h-[200px]', 'h-[260px]', 'h-[260px]', 'h-[200px]', 'h-[200px]'];
            @endphp

            @if ($galeria->isNotEmpty())
                <div class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($galeria as $i => $img)
                        @php
                            $alto = $alturas[$i % count($alturas)] ?? 'h-[260px]';
                        @endphp
                        <div class="gal-item reveal reveal-d{{ min($i+1,5) }} group relative overflow-hidden rounded-2xl border border-white/10 {{ $alto }}">
                            <img src="{{ $img->url }}" alt="{{ $img->titulo ?? 'Obra RNFC' }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#071426] via-[#071426]/40 to-transparent"></div>
                            <div class="absolute inset-x-0 bottom-0 p-4">
                                <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/55">RNFC · campo</div>
                                @if ($img->titulo)
                                    <div class="font-display text-lg font-extrabold text-white">{{ $img->titulo }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="reveal mt-14 glass rounded-2xl px-6 py-16 text-center">
                    <p class="text-sm text-[var(--c-text-dim)]">
                        Galería disponible próximamente. Las imágenes se publicarán desde el panel de administración.
                    </p>
                </div>
            @endif
        </div>
    </section>

    {{-- ================================================================
         CTA FINAL / CONTACTO
    ================================================================ --}}
    <section id="contacto" class="relative overflow-hidden border-t border-white/5 py-16 md:py-28">
        <div class="absolute inset-0 -z-10 bg-[#05101e]"></div>
        <div class="bg-blueprint-fine absolute inset-0 -z-10 opacity-30"></div>

        <div class="relative mx-auto max-w-5xl px-4 text-center md:px-8">
            <div class="eyebrow eyebrow-center">Conversemos</div>
            <h2 class="mt-5 font-display text-2xl font-extrabold tracking-tight text-white sm:text-3xl md:text-5xl">
                ¿Tienes una obra que supervisar?<br>
                <span class="text-white/70">Hablemos del proyecto.</span>
            </h2>
            <p class="mx-auto mt-5 max-w-2xl text-base text-white/80 md:text-lg">
                Recibe una propuesta técnica y comercial elaborada por nuestro equipo de ingenieros.
            </p>

            @if (session('success'))
                <div class="mx-auto mt-6 max-w-xl rounded-xl border border-emerald-400/40 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('landing.contacto') }}" method="POST" class="mx-auto mt-10 grid max-w-2xl gap-3 text-left">
                @csrf
                <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden">

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="text-[11px] font-bold uppercase tracking-wider text-white/60">Nombre</label>
                        <input required name="nombre" value="{{ old('nombre') }}" class="mt-1 w-full rounded-lg border border-white/15 bg-white/[0.04] px-4 py-3 text-sm text-white placeholder-white/40 focus:border-[#60a5fa] focus:outline-none focus:ring-2 focus:ring-[#2563EB]/40" placeholder="Nombre completo">
                        @error('nombre')<p class="mt-1 text-xs text-red-300">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="text-[11px] font-bold uppercase tracking-wider text-white/60">Correo</label>
                        <input required type="email" name="correo" value="{{ old('correo') }}" class="mt-1 w-full rounded-lg border border-white/15 bg-white/[0.04] px-4 py-3 text-sm text-white placeholder-white/40 focus:border-[#60a5fa] focus:outline-none focus:ring-2 focus:ring-[#2563EB]/40" placeholder="tu@correo.com">
                        @error('correo')<p class="mt-1 text-xs text-red-300">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="text-[11px] font-bold uppercase tracking-wider text-white/60">Teléfono</label>
                        <input name="telefono" value="{{ old('telefono') }}" class="mt-1 w-full rounded-lg border border-white/15 bg-white/[0.04] px-4 py-3 text-sm text-white placeholder-white/40 focus:border-[#60a5fa] focus:outline-none focus:ring-2 focus:ring-[#2563EB]/40" placeholder="+51 ...">
                    </div>
                    <div>
                        <label class="text-[11px] font-bold uppercase tracking-wider text-white/60">Asunto</label>
                        <input name="asunto" value="{{ old('asunto') }}" class="mt-1 w-full rounded-lg border border-white/15 bg-white/[0.04] px-4 py-3 text-sm text-white placeholder-white/40 focus:border-[#60a5fa] focus:outline-none focus:ring-2 focus:ring-[#2563EB]/40" placeholder="Supervisión / consultoría / propuesta">
                    </div>
                </div>

                <div>
                    <label class="text-[11px] font-bold uppercase tracking-wider text-white/60">Mensaje</label>
                    <textarea required name="mensaje" rows="4" class="mt-1 w-full rounded-lg border border-white/15 bg-white/[0.04] px-4 py-3 text-sm text-white placeholder-white/40 focus:border-[#60a5fa] focus:outline-none focus:ring-2 focus:ring-[#2563EB]/40" placeholder="Cuéntanos del proyecto, entidad, monto referencial y plazo...">{{ old('mensaje') }}</textarea>
                    @error('mensaje')<p class="mt-1 text-xs text-red-300">{{ $message }}</p>@enderror
                </div>

                <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-white/55">Al enviar aceptas que RNFC se comunique contigo respecto a tu consulta.</p>
                    <button type="submit" class="btn-gold inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-bold">
                        Enviar consulta
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7Z"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- ================================================================
         FOOTER
    ================================================================ --}}
    <footer class="relative border-t border-white/5 bg-[#04101e] pt-12 pb-8 md:pt-16">
        <div class="bg-blueprint-fine absolute inset-0 opacity-20"></div>

        <div class="relative mx-auto max-w-7xl px-4 md:px-8">
            <div class="grid gap-10 sm:grid-cols-2 md:gap-12 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
                <div>
                    <img src="/brand/rnfc-logo.png" alt="RNFC" class="h-12 w-auto">
                    <p class="mt-5 max-w-sm text-sm leading-relaxed text-[var(--c-text-dim)]">
                        Firma peruana especializada en supervisión técnica, consultoría de obras y
                        gestión documental inteligente — operando bajo certificaciones ISO
                        9001, 14001 y 37001.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach (['ISO 9001', 'ISO 14001', 'ISO 37001', 'MTPE 089-2025'] as $b)
                            <span class="rounded-full border border-white/10 bg-white/[0.03] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white/70">{{ $b }}</span>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-white">Empresa</h4>
                    <ul class="mt-4 space-y-2.5 text-sm text-[var(--c-text-dim)]">
                        <li><a href="#servicios" class="hover:text-white">Servicios</a></li>
                        <li><a href="#plataforma" class="hover:text-white">Plataforma</a></li>
                        <li><a href="#experiencia" class="hover:text-white">Experiencia</a></li>
                        <li><a href="#certificaciones" class="hover:text-white">Certificaciones</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-white">Plataforma</h4>
                    <ul class="mt-4 space-y-2.5 text-sm text-[var(--c-text-dim)]">
                        <li><a href="{{ route('login') }}" class="hover:text-white">Iniciar sesión</a></li>
                        <li><a href="{{ route('verificar.form') }}" class="hover:text-white">Verificar certificado</a></li>
                        <li><a href="https://icocert.pe" target="_blank" rel="noopener" class="hover:text-white">ICO Cert</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-[11px] font-bold uppercase tracking-[0.2em] text-white">Contacto</h4>
                    <ul class="mt-4 space-y-2.5 text-sm text-[var(--c-text-dim)]">
                        <li>Jr. Jauregui 1235, Juliaca · Puno</li>
                        <li><a href="mailto:contacto@rnfcconsultoria.com" class="hover:text-white">contacto@rnfcconsultoria.com</a></li>
                        <li>RUC 10421559029</li>
                    </ul>
                    <div class="mt-5 flex items-center gap-2">
                        @foreach ([
                            ['mailto:contacto@rnfcconsultoria.com', 'M4 4h16v16H4zM4 4l8 8 8-8'],
                            ['https://wa.me/51999999999', 'M22 12a10 10 0 1 1-3.7-7.74L22 2l-1.7 5.7A10 10 0 0 1 22 12Z'],
                            ['#', 'M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6ZM2 9h4v12H2z'],
                        ] as $s)
                            <a href="{{ $s[0] }}" class="inline-flex size-9 items-center justify-center rounded-lg border border-white/10 bg-white/[0.03] text-white/70 transition-colors hover:border-[#60a5fa] hover:text-white">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $s[1] }}"/></svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-white/5 pt-6 text-xs text-white/45 md:flex-row">
                <div>© {{ date('Y') }} RNFC Consultor de Obras · Todos los derechos reservados</div>
                <div class="flex items-center gap-4">
                    <span>Hecho en Perú · Juliaca, Puno</span>
                </div>
            </div>
        </div>
    </footer>

    {{-- ================================================================
         SCRIPTS
    ================================================================ --}}
    <script>
        (function () {
            const nav = document.getElementById('nav');
            if (nav) {
                const onScroll = () => {
                    if (window.scrollY > 40) nav.classList.add('scrolled');
                    else nav.classList.remove('scrolled');
                };
                onScroll();
                window.addEventListener('scroll', onScroll, { passive: true });
            }

            // mobile menu
            const navToggle = document.getElementById('navToggle');
            const navMobile = document.getElementById('navMobile');
            const iconOpen = document.getElementById('navIconOpen');
            const iconClose = document.getElementById('navIconClose');
            if (navToggle && navMobile) {
                const close = () => {
                    navMobile.classList.add('hidden');
                    iconOpen?.classList.remove('hidden');
                    iconClose?.classList.add('hidden');
                };
                navToggle.addEventListener('click', () => {
                    const isHidden = navMobile.classList.toggle('hidden');
                    iconOpen?.classList.toggle('hidden', !isHidden);
                    iconClose?.classList.toggle('hidden', isHidden);
                });
                navMobile.querySelectorAll('a').forEach((a) => a.addEventListener('click', close));
            }

            const els = document.querySelectorAll('.reveal');
            if ('IntersectionObserver' in window && els.length) {
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((e) => {
                        if (e.isIntersecting) {
                            e.target.classList.add('in');
                            io.unobserve(e.target);
                        }
                    });
                }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
                els.forEach((el) => io.observe(el));
            } else {
                els.forEach((el) => el.classList.add('in'));
            }

            const counters = document.querySelectorAll('[data-counter]');
            const animate = (el) => {
                const target = parseInt(el.dataset.to || '0', 10);
                const suffix = el.dataset.suffix || '';
                const prefix = el.dataset.prefix || '';
                const duration = 1500;
                const start = performance.now();
                const step = (now) => {
                    const p = Math.min(1, (now - start) / duration);
                    const eased = 1 - Math.pow(1 - p, 3);
                    el.textContent = prefix + Math.round(target * eased) + suffix;
                    if (p < 1) requestAnimationFrame(step);
                };
                requestAnimationFrame(step);
            };
            if ('IntersectionObserver' in window && counters.length) {
                const co = new IntersectionObserver((entries) => {
                    entries.forEach((e) => {
                        if (e.isIntersecting) {
                            animate(e.target);
                            co.unobserve(e.target);
                        }
                    });
                }, { threshold: 0.5 });
                counters.forEach((el) => co.observe(el));
            }
        })();
    </script>
</body>
</html>
