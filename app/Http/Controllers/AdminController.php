<?php

namespace App\Http\Controllers;

use App\Enums\RolGlobal;
use App\Services\DashboardAdminService;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Panel de administración con KPIs cruzados, ranking de almacenamiento
     * y actividad reciente unificada. Sólo accesible para roles administrativos.
     */
    public function __invoke(DashboardAdminService $dashboard): Response
    {
        abort_unless(
            request()->user()?->hasAnyRole(RolGlobal::rolesAdministrativos()),
            403,
        );

        return Inertia::render('admin/index', [
            'kpis' => $dashboard->kpis(),
            'estadosObras' => $dashboard->estadosObras(),
            'certificadosPorTipo' => $dashboard->certificadosPorTipo(),
            'almacenamiento' => $dashboard->rankingAlmacenamiento(),
            'documentosPorObra' => $dashboard->topObrasDocumentos(),
            'actividadReciente' => $dashboard->actividadReciente(),
            'usuariosActivos' => $dashboard->topUsuariosActivos(),
        ]);
    }
}
