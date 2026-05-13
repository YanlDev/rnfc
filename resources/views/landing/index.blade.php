<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#0b2545">

    {{-- SEO básico --}}
    @php
        $tituloSeo = 'RNFC Consultor de Obras — Supervisión, Consultoría e Ingeniería en el Perú';
        $descripcionSeo = 'RNFC Consultor de Obras: supervisión, consultoría, arquitectura e ingeniería para obras públicas y privadas en el Perú. Liderado por el Ing. Roger Neptali Flores Coaquira. Certificado ISO 9001, 14001 y 37001.';
        $urlActual = url('/');
        $imagenSeo = asset('brand/rnfc-logo.png');
    @endphp

    <title>{{ $tituloSeo }}</title>
    <meta name="description" content="{{ $descripcionSeo }}">
    <link rel="canonical" href="{{ $urlActual }}">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_PE">
    <meta property="og:site_name" content="RNFC Consultor de Obras">
    <meta property="og:title" content="{{ $tituloSeo }}">
    <meta property="og:description" content="{{ $descripcionSeo }}">
    <meta property="og:url" content="{{ $urlActual }}">
    <meta property="og:image" content="{{ $imagenSeo }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $tituloSeo }}">
    <meta name="twitter:description" content="{{ $descripcionSeo }}">
    <meta name="twitter:image" content="{{ $imagenSeo }}">

    {{-- Schema.org Organization + LocalBusiness --}}
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
    <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Barlow:wght@500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        :root {
            --azul: #0b2545;
            --azul-2: #145694;
            --azul-3: #1d6cb3;
            --amarillo: #ffd21c;
            --amarillo-2: #f5b800;
        }
        html, body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        body { font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11'; letter-spacing: -0.005em; }
        h1, h2, h3, h4, .font-display {
            font-family: 'Barlow', 'Inter', system-ui, sans-serif;
            letter-spacing: -0.015em;
        }
        .display-tight { letter-spacing: -0.03em; line-height: 0.95; }

        /* ============ HERO ============ */
        .hero-photo {
            background-image:
                linear-gradient(135deg, rgba(11,37,69,0.88) 0%, rgba(11,37,69,0.65) 50%, rgba(11,37,69,0.88) 100%),
                url('/brand/supervision.png');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            animation: kenburns 30s ease-in-out infinite alternate;
        }
        @keyframes kenburns {
            0%   { background-size: 110%; background-position: 50% 50%; }
            100% { background-size: 120%; background-position: 55% 48%; }
        }
        .hero-grid {
            background-image:
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 60px 60px;
            mask-image: radial-gradient(ellipse at center, black 30%, transparent 75%);
        }
        .hero-vignette {
            background: radial-gradient(ellipse at center, transparent 0%, rgba(11,37,69,0.55) 85%);
        }

        /* ============ NAVBAR TRANSPARENTE → BLANCO ============ */
        .nav-shell {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 50;
            transition: background-color 0.35s ease, border-color 0.35s ease, backdrop-filter 0.35s ease, box-shadow 0.35s ease;
            background-color: transparent;
            border-bottom: 1px solid transparent;
        }
        .nav-shell .nav-link { color: rgba(255,255,255,0.92); }
        .nav-shell .nav-link:hover { color: #ffd21c; }
        .nav-shell .nav-login {
            color: #fff;
            border-color: rgba(255,255,255,0.55);
            background-color: rgba(255,255,255,0.08);
            backdrop-filter: blur(6px);
        }
        .nav-shell .nav-login:hover {
            background-color: #ffd21c;
            color: #0b2545;
            border-color: #ffd21c;
        }
        .nav-shell .nav-logo {
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.45));
            transition: filter 0.3s ease;
        }
        .nav-shell.scrolled .nav-logo {
            filter: none;
        }
        .nav-shell.scrolled {
            background-color: rgba(255,255,255,0.92);
            backdrop-filter: blur(10px);
            border-bottom-color: rgba(0,0,0,0.06);
            box-shadow: 0 6px 24px -10px rgba(11,37,69,0.18);
        }
        .nav-shell.scrolled .nav-link { color: rgb(71, 85, 105); }
        .nav-shell.scrolled .nav-link:hover { color: #145694; }
        .nav-shell.scrolled .nav-login {
            color: #145694;
            border-color: #145694;
            background-color: transparent;
            backdrop-filter: none;
        }
        .nav-shell.scrolled .nav-login:hover {
            background-color: #145694;
            color: #fff;
        }
        /* spacer ya no se necesita: el hero está debajo del nav fijo */

        /* ============ HERO: logo + isos sin caja ============ */
        .hero-logo {
            filter:
                drop-shadow(0 8px 30px rgba(0,0,0,0.55))
                drop-shadow(0 0 50px rgba(11,37,69,0.55));
            animation: floaty 6s ease-in-out infinite alternate;
        }
        .hero-isos {
            position: relative;
            height: auto;
            filter:
                drop-shadow(0 10px 30px rgba(0,0,0,0.65))
                drop-shadow(0 0 50px rgba(255,210,28,0.3));
        }
        /* halo radial detrás (sin caja visible) */
        .hero-fade > div:last-child { position: relative; }
        .hero-fade > div:last-child::before {
            content: '';
            position: absolute;
            inset: -10%;
            background: radial-gradient(ellipse at center, rgba(255,210,28,0.18) 0%, rgba(20,86,148,0.18) 35%, transparent 70%);
            z-index: -1;
            filter: blur(40px);
            pointer-events: none;
        }
        @keyframes floaty {
            0%   { transform: translateY(0); }
            100% { transform: translateY(-10px); }
        }
        @media (prefers-reduced-motion: reduce) {
            .hero-logo { animation: none; }
        }

        /* ============ MARCADOR DE SECCIÓN ============ */
        .seccion-titulo { position: relative; display: inline-block; }
        .seccion-titulo::after {
            content: '';
            position: absolute;
            left: 0; bottom: -10px;
            width: 64px; height: 4px;
            border-radius: 2px;
            background: linear-gradient(90deg, var(--azul-2), var(--amarillo));
        }
        .pattern-dots {
            background-image: radial-gradient(rgba(20,86,148,0.12) 1px, transparent 1px);
            background-size: 18px 18px;
        }

        /* ============ REVEAL ON SCROLL ============ */
        .reveal { opacity: 0; transform: translateY(28px); transition: opacity 0.7s cubic-bezier(.2,.7,.2,1), transform 0.7s cubic-bezier(.2,.7,.2,1); }
        .reveal.in { opacity: 1; transform: none; }
        .reveal-delay-1 { transition-delay: 0.08s; }
        .reveal-delay-2 { transition-delay: 0.16s; }
        .reveal-delay-3 { transition-delay: 0.24s; }
        .reveal-delay-4 { transition-delay: 0.32s; }
        .reveal-delay-5 { transition-delay: 0.40s; }

        /* ============ HERO TEXTO ============ */
        .hero-fade > * { opacity: 0; transform: translateY(18px); animation: heroIn 0.9s cubic-bezier(.2,.7,.2,1) forwards; }
        .hero-fade > *:nth-child(1) { animation-delay: 0.10s; }
        .hero-fade > *:nth-child(2) { animation-delay: 0.25s; }
        .hero-fade > *:nth-child(3) { animation-delay: 0.40s; }
        .hero-fade > *:nth-child(4) { animation-delay: 0.55s; }
        .hero-fade > *:nth-child(5) { animation-delay: 0.70s; }
        @keyframes heroIn { to { opacity: 1; transform: none; } }

        /* ============ BADGE ISO ============ */
        .iso-card {
            position: relative;
            transition: transform 0.35s cubic-bezier(.2,.7,.2,1), box-shadow 0.35s ease;
        }
        .iso-card:hover { transform: translateY(-6px); }
        .iso-card::before {
            content: '';
            position: absolute; inset: -1px;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(135deg, var(--azul-2), var(--amarillo), var(--azul-2));
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
                    mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.35s ease;
        }
        .iso-card:hover::before { opacity: 1; }
        .iso-shine {
            position: absolute; inset: 0;
            background: linear-gradient(115deg, transparent 30%, rgba(255,210,28,0.18) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.9s ease;
            pointer-events: none;
        }
        .iso-card:hover .iso-shine { transform: translateX(100%); }

        /* ============ DETALLES ============ */
        details > summary { list-style: none; cursor: pointer; }
        details > summary::-webkit-details-marker { display: none; }

        /* ============ ACCESIBILIDAD ============ */
        @media (prefers-reduced-motion: reduce) {
            .hero-photo { animation: none; }
            .reveal, .hero-fade > * { transition: none; animation: none; opacity: 1; transform: none; }
        }
    </style>
</head>
<body class="bg-white text-slate-800 antialiased">

    {{-- =============== NAVBAR (transparente sobre hero, blanco al hacer scroll) =============== --}}
    <header id="nav" class="nav-shell">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 md:px-8">
            <a href="#inicio" class="nav-logo flex items-center gap-3">
                <img src="/brand/rnfc-logo.png" alt="RNFC Consultor de Obras" class="h-[60px] w-auto">
                <span class="sr-only">RNFC Consultor de Obras</span>
            </a>
            <div class="flex items-center gap-3 md:gap-8">
                <nav class="hidden items-center gap-6 lg:flex xl:gap-8">
                    <a href="#nosotros" class="nav-link text-sm font-semibold transition-colors">Nosotros</a>
                    <a href="#certificaciones" class="nav-link text-sm font-semibold transition-colors">Certificaciones</a>
                    <a href="#experiencia" class="nav-link text-sm font-semibold transition-colors">Experiencia</a>
                    <a href="{{ route('verificar.form') }}" class="nav-link text-sm font-semibold transition-colors">Verificar certificado</a>
                </nav>
                <a href="{{ route('login') }}"
                   class="nav-login rounded-md border px-4 py-2 text-sm font-semibold transition-colors">
                    Iniciar sesión
                </a>
            </div>
        </div>
    </header>

    {{-- =============== HERO =============== --}}
    <section id="inicio" class="relative isolate min-h-[88svh] overflow-hidden text-white">
        <div class="hero-photo absolute inset-0 -z-10"></div>

        <div class="relative mx-auto flex min-h-[88svh] max-w-5xl flex-col items-center justify-center px-4 pt-32 pb-20 text-center md:px-8">
            <div class="hero-fade">
                <h1 class="font-display text-4xl font-bold leading-[1.05] md:text-5xl lg:text-6xl xl:text-[4.5rem]">
                    Llevamos su obra<br>
                    <span class="text-[#ffd21c]">del expediente al cierre.</span>
                </h1>

                <p class="mx-auto mt-8 max-w-2xl text-base leading-relaxed text-white/85 md:text-lg">
                    Firma peruana especializada en supervisión y consultoría de proyectos de
                    infraestructura pública y privada, liderada por el
                    <strong class="font-semibold text-white">Ing. Roger Neptali Flores Coaquira</strong>.
                </p>

                <div class="mt-14 flex flex-wrap items-center justify-center gap-6 md:gap-10 lg:gap-12">
                    <img src="/brand/ISO 9001.png"  alt="ISO 9001"  class="h-20 w-auto drop-shadow-[0_6px_20px_rgba(0,0,0,0.5)] md:h-24 lg:h-28">
                    <img src="/brand/ISO 14001.png" alt="ISO 14001" class="h-20 w-auto drop-shadow-[0_6px_20px_rgba(0,0,0,0.5)] md:h-24 lg:h-28">
                    <img src="/brand/ISO 37001.png" alt="ISO 37001" class="h-20 w-auto drop-shadow-[0_6px_20px_rgba(0,0,0,0.5)] md:h-24 lg:h-28">
                </div>
            </div>
        </div>
    </section>

    {{-- =============== NOSOTROS =============== --}}
    <section id="nosotros" class="bg-white py-20">
        <div class="mx-auto max-w-5xl px-4 md:px-8">
            <div class="reveal text-center">
                <span class="text-xs font-bold tracking-widest text-slate-500 uppercase">Quiénes somos</span>
                <h2 class="seccion-titulo font-display mt-2 inline-block text-3xl font-bold text-slate-900 md:text-4xl">Nosotros</h2>
            </div>

            <div class="reveal reveal-delay-1 mt-12 space-y-5 text-base leading-relaxed text-slate-600 md:text-lg">
                <p>
                    <strong class="text-slate-900">RNFC Consultor de Obras</strong> es una firma peruana
                    dedicada a la supervisión y consultoría de proyectos de ingeniería, arquitectura
                    y construcción. Trabajamos junto a entidades públicas y privadas para garantizar
                    la calidad técnica, el cumplimiento contractual y la entrega en plazo de cada obra.
                </p>
                <p>
                    Nuestro fundador, el <strong class="text-slate-900">Ing. Roger Neptali Flores Coaquira</strong>,
                    cuenta con más de quince años de experiencia liderando equipos multidisciplinarios
                    en obras de infraestructura vial, edificaciones, saneamiento, electromecánicas y
                    proyectos especiales a nivel nacional.
                </p>
            </div>

            <div class="mt-14 grid gap-6 md:grid-cols-2">
                <div class="reveal reveal-delay-2 rounded-xl border border-slate-200 bg-white p-8 transition-shadow hover:shadow-md">
                    <div class="font-display text-xs font-bold tracking-[0.25em] text-slate-400 uppercase">01</div>
                    <h3 class="font-display mt-2 text-2xl font-bold text-slate-900">Misión</h3>
                    <div class="mt-3 h-px w-12 bg-slate-300"></div>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600 md:text-base">
                        Brindar servicios de supervisión y consultoría con los más altos estándares
                        técnicos, normativos y éticos, contribuyendo al desarrollo de infraestructura
                        sostenible en el país.
                    </p>
                </div>
                <div class="reveal reveal-delay-3 rounded-xl border border-slate-200 bg-white p-8 transition-shadow hover:shadow-md">
                    <div class="font-display text-xs font-bold tracking-[0.25em] text-slate-400 uppercase">02</div>
                    <h3 class="font-display mt-2 text-2xl font-bold text-slate-900">Visión</h3>
                    <div class="mt-3 h-px w-12 bg-slate-300"></div>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600 md:text-base">
                        Ser una firma consultora referente en el Perú por la rigurosidad técnica,
                        la transparencia en la gestión y la calidad de las obras que supervisamos
                        y diseñamos.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- =============== CERTIFICACIONES =============== --}}
    <section id="certificaciones" class="relative overflow-hidden bg-[#0b2545] py-20 text-white">
        <div class="hero-grid absolute inset-0 opacity-30"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-[#0b2545] via-[#0b2545] to-[#08203b]"></div>

        <div class="relative mx-auto max-w-7xl px-4 md:px-8">
            <div class="mx-auto max-w-2xl text-center reveal">
                <span class="text-xs font-bold tracking-widest text-[#ffd21c] uppercase">Calidad certificada</span>
                <h2 class="seccion-titulo font-display mt-2 inline-block text-3xl font-bold text-white md:text-4xl">Certificaciones</h2>
                <p class="mt-10 text-base text-white/80">
                    RNFC opera bajo sistemas de gestión certificados por organismos acreditados,
                    garantizando estándares internacionales de calidad, sostenibilidad y ética en cada obra.
                </p>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $certificaciones = [
                        [
                            'logo'   => '/brand/ISO 9001.png',
                            'codigo' => 'ISO 9001:2015',
                            'titulo' => 'Sistema de Gestión de la Calidad',
                            'desc'   => 'Procesos auditables, control documental y mejora continua aplicada a cada proyecto.',
                        ],
                        [
                            'logo'   => '/brand/ISO 14001.png',
                            'codigo' => 'ISO 14001:2015',
                            'titulo' => 'Sistema de Gestión Ambiental',
                            'desc'   => 'Identificación, control y mitigación de impactos ambientales en obra.',
                        ],
                        [
                            'logo'   => '/brand/ISO 37001.png',
                            'codigo' => 'ISO 37001:2025',
                            'titulo' => 'Sistema de Gestión Antisoborno',
                            'desc'   => 'Política de cero soborno, debida diligencia y transparencia con todas las partes.',
                        ],
                        [
                            'logo'   => null,
                            'codigo' => 'MTPE · 089-2025',
                            'titulo' => 'Empresa Promocional · Discapacidad',
                            'desc'   => 'Registro N° 089-2025-GR PUNO/DRTPE/ZTPE/JUL. Compromiso con la inclusión laboral.',
                            'isMtpe' => true,
                        ],
                    ];
                @endphp

                @foreach ($certificaciones as $i => $cert)
                    <div class="iso-card reveal reveal-delay-{{ min($i + 1, 5) }} group relative overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04] p-6 backdrop-blur">
                        <span class="iso-shine"></span>

                        <div class="relative mx-auto flex h-32 items-center justify-center">
                            @if (!empty($cert['isMtpe']))
                                {{-- Insignia MTPE (sin logo PNG) --}}
                                <svg viewBox="0 0 120 120" class="h-full w-auto drop-shadow-[0_0_20px_rgba(255,210,28,0.35)]" xmlns="http://www.w3.org/2000/svg">
                                    <defs>
                                        <linearGradient id="grad-mtpe" x1="0" y1="0" x2="1" y2="1">
                                            <stop offset="0%" stop-color="#ffd21c"/>
                                            <stop offset="100%" stop-color="#f59e0b"/>
                                        </linearGradient>
                                    </defs>
                                    <circle cx="60" cy="60" r="54" fill="url(#grad-mtpe)" stroke="#fff" stroke-width="2"/>
                                    <circle cx="60" cy="60" r="44" fill="none" stroke="#0b2545" stroke-width="1.5" stroke-dasharray="3 3"/>
                                    <text x="60" y="50" text-anchor="middle" font-family="Barlow, sans-serif" font-size="13" font-weight="800" fill="#0b2545">MTPE</text>
                                    <text x="60" y="68" text-anchor="middle" font-family="Barlow, sans-serif" font-size="9" font-weight="700" fill="#0b2545">REGISTRO</text>
                                    <text x="60" y="80" text-anchor="middle" font-family="Barlow, sans-serif" font-size="9" font-weight="700" fill="#0b2545">089-2025</text>
                                    <path d="M40 88 L60 96 L80 88" fill="none" stroke="#0b2545" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            @else
                                <img src="{{ $cert['logo'] }}" alt="{{ $cert['codigo'] }}"
                                     class="h-full w-auto object-contain drop-shadow-[0_8px_24px_rgba(0,0,0,0.45)] transition-transform duration-500 group-hover:scale-105">
                            @endif
                        </div>

                        <h3 class="font-display mt-5 text-center text-base font-bold text-white">
                            {{ $cert['codigo'] }}
                        </h3>
                        <p class="mt-1 text-center text-xs font-semibold uppercase tracking-wider text-[#ffd21c]">
                            {{ $cert['titulo'] }}
                        </p>
                        <p class="mt-3 text-center text-sm leading-relaxed text-white/75">
                            {{ $cert['desc'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            <p class="mx-auto mt-12 max-w-3xl text-center text-sm text-white/60 reveal">
                Certificaciones emitidas por <strong class="text-white/80">International Certification Organization (ICO)</strong>
                — la autenticidad de los certificados puede verificarse en
                <a href="https://icocert.pe" target="_blank" rel="noopener" class="text-[#ffd21c] underline underline-offset-4 hover:text-white">icocert.pe</a>.
            </p>
        </div>
    </section>

    {{-- =============== EXPERIENCIA PROFESIONAL =============== --}}
    <section id="experiencia" class="bg-white py-20">
        <div class="mx-auto max-w-7xl px-4 md:px-8">
            <div class="mx-auto max-w-2xl text-center reveal">
                <span class="text-xs font-bold tracking-widest text-[#145694] uppercase">Respaldo</span>
                <h2 class="seccion-titulo font-display mt-2 inline-block text-3xl font-bold text-slate-900 md:text-4xl">Experiencia profesional</h2>
                <p class="mt-10 text-base text-slate-600">
                    Hemos trabajado con instituciones públicas y privadas en todo el país.
                </p>
            </div>

            <div class="mt-12 grid gap-8 lg:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 reveal reveal-delay-1 transition-shadow hover:shadow-lg">
                    <h3 class="font-display mb-4 flex items-center gap-2 font-bold text-[#145694]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                        Entidades contratantes
                    </h3>
                    <ul class="space-y-2 text-sm text-slate-700">
                        <li class="border-l-2 border-[#ffd21c] pl-3">Gobiernos Regionales</li>
                        <li class="border-l-2 border-[#ffd21c] pl-3">Municipalidades Provinciales y Distritales</li>
                        <li class="border-l-2 border-[#ffd21c] pl-3">Ministerio de Transportes y Comunicaciones</li>
                        <li class="border-l-2 border-[#ffd21c] pl-3">Programa Nacional de Saneamiento</li>
                        <li class="border-l-2 border-[#ffd21c] pl-3">Empresas constructoras privadas</li>
                        <li class="border-l-2 border-[#ffd21c] pl-3">Consultoras y especialistas asociados</li>
                    </ul>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 reveal reveal-delay-2 transition-shadow hover:shadow-lg">
                    <h3 class="font-display mb-4 flex items-center gap-2 font-bold text-[#145694]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0"/></svg>
                        Reconocimientos
                    </h3>
                    <ul class="space-y-2 text-sm text-slate-700">
                        <li class="border-l-2 border-[#ffd21c] pl-3">Colegio de Ingenieros del Perú (CIP) — habilitación vigente</li>
                        <li class="border-l-2 border-[#ffd21c] pl-3">Certificaciones técnicas en supervisión y QA/QC</li>
                        <li class="border-l-2 border-[#ffd21c] pl-3">Cursos de especialización en gestión de proyectos</li>
                        <li class="border-l-2 border-[#ffd21c] pl-3">Capacitaciones SSOMA y medio ambiente</li>
                    </ul>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 reveal reveal-delay-3 transition-shadow hover:shadow-lg">
                    <h3 class="font-display mb-4 flex items-center gap-2 font-bold text-[#145694]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/></svg>
                        Estándares de trabajo
                    </h3>
                    <ul class="space-y-2 text-sm text-slate-700">
                        <li class="border-l-2 border-[#ffd21c] pl-3">Normativa Ley de Contrataciones del Estado</li>
                        <li class="border-l-2 border-[#ffd21c] pl-3">Reglamento Nacional de Edificaciones</li>
                        <li class="border-l-2 border-[#ffd21c] pl-3">ISO 9001, 14001 y 37001 certificadas</li>
                        <li class="border-l-2 border-[#ffd21c] pl-3">Buenas prácticas BIM y gestión documental</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- =============== FOOTER MINIMALISTA =============== --}}
    <footer class="border-t border-slate-200 bg-white py-4">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 text-xs text-slate-500 md:flex-row md:px-8">
            <img src="/brand/rnfc-logo.png" alt="RNFC" class="h-8 w-auto">
            <div class="flex items-center gap-5">
                <a href="{{ route('verificar.form') }}" class="font-semibold text-slate-700 hover:text-[#145694]">Verificar certificado</a>
                <span>© {{ date('Y') }} RNFC · RUC 10421559029</span>
            </div>
        </div>
    </footer>

    {{-- =============== SCRIPTS: reveal + contadores =============== --}}
    <script>
        (function () {
            // Navbar: transparente → blanco al hacer scroll
            const nav = document.getElementById('nav');
            if (nav) {
                const onScroll = () => {
                    if (window.scrollY > 40) nav.classList.add('scrolled');
                    else nav.classList.remove('scrolled');
                };
                onScroll();
                window.addEventListener('scroll', onScroll, { passive: true });
            }

            // Reveal on scroll
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

            // Animated counters
            const counters = document.querySelectorAll('[data-counter]');
            const animateCounter = (el) => {
                const target = parseInt(el.dataset.to || '0', 10);
                const suffix = el.dataset.suffix || '';
                const prefix = el.dataset.prefix || '';
                const duration = 1400;
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
                            animateCounter(e.target);
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
