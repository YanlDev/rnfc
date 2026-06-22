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
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300..700&family=Geist+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
