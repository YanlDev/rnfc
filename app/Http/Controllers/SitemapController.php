<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Sitemap básico para la landing pública (single-page con anclas).
     * Sólo expone URLs públicas, nunca rutas autenticadas.
     */
    public function __invoke(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $hoy = now()->format('Y-m-d');

        $urls = [
            ['loc' => $base.'/', 'priority' => '1.0', 'changefreq' => 'monthly'],
            ['loc' => $base.'/#nosotros', 'priority' => '0.8', 'changefreq' => 'yearly'],
            ['loc' => $base.'/#servicios', 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => $base.'/#proyectos', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => $base.'/#experiencia', 'priority' => '0.7', 'changefreq' => 'yearly'],
            ['loc' => $base.'/#contacto', 'priority' => '0.9', 'changefreq' => 'yearly'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$u['loc']}</loc>\n";
            $xml .= "    <lastmod>{$hoy}</lastmod>\n";
            $xml .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$u['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
