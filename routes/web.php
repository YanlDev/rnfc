<?php

use App\Http\Controllers\Admin\InvitacionGlobalController;
use App\Http\Controllers\Admin\UsuariosController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AsientoCuadernoController;
use App\Http\Controllers\CalendarioSelectorController;
use App\Http\Controllers\CarpetaController;
use App\Http\Controllers\CertificadoController;
use App\Http\Controllers\CuadernoSelectorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\EquipoGlobalController;
use App\Http\Controllers\EquipoObraController;
use App\Http\Controllers\EventoCalendarioController;
use App\Http\Controllers\InvitacionController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\ObraController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\VerificacionController;
use Illuminate\Support\Facades\Route;

// Landing institucional pública (Blade + Tailwind, SEO-friendly)
Route::get('/', [LandingController::class, 'home'])->name('home');
Route::post('/contacto', [LandingController::class, 'enviarMensaje'])->name('landing.contacto');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Verificación pública de certificados (sin auth)
Route::get('verificar', [VerificacionController::class, 'form'])->name('verificar.form');
Route::post('verificar', [VerificacionController::class, 'buscar'])->name('verificar.buscar');
Route::get('verificar/{codigo}', [VerificacionController::class, 'mostrar'])
    ->name('verificar')
    ->where('codigo', 'RNFC-[0-9]{4}-[A-Z0-9]{6}');

// Página pública de invitación (el link del correo)
Route::get('invitaciones/{token}', [InvitacionController::class, 'mostrar'])
    ->name('invitaciones.mostrar')
    ->where('token', '[A-Za-z0-9]{64}');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Panel de Administración (acceso restringido a admin/gerente general)
    Route::get('admin', AdminController::class)->name('admin.index');

    // Gestión de usuarios (admin / gerente general)
    Route::get('admin/usuarios', [UsuariosController::class, 'index'])
        ->name('admin.usuarios.index');
    Route::patch('admin/usuarios/{usuario}/toggle-activo', [UsuariosController::class, 'toggleActivo'])
        ->name('admin.usuarios.toggle-activo');
    Route::patch('admin/usuarios/{usuario}/rol', [UsuariosController::class, 'cambiarRol'])
        ->name('admin.usuarios.rol');
    Route::post('admin/invitar', [InvitacionGlobalController::class, 'store'])
        ->name('admin.invitar');

    // Obras
    Route::resource('obras', ObraController::class);

    // Gestor documental: carpetas de una obra
    Route::get('obras/{obra}/documentos', [CarpetaController::class, 'index'])->name('obras.documentos.index');
    Route::post('obras/{obra}/carpetas', [CarpetaController::class, 'store'])->name('obras.carpetas.store');
    Route::patch('obras/{obra}/carpetas/{carpeta}', [CarpetaController::class, 'update'])->name('obras.carpetas.update');
    Route::delete('obras/{obra}/carpetas/{carpeta}', [CarpetaController::class, 'destroy'])->name('obras.carpetas.destroy');
    Route::post('obras/{obra}/carpetas/plantilla', [CarpetaController::class, 'aplicarPlantilla'])->name('obras.carpetas.plantilla');

    // Documentos
    Route::post('obras/{obra}/carpetas/{carpeta}/documentos', [DocumentoController::class, 'store'])->name('obras.documentos.store');
    Route::post('obras/{obra}/documentos/{documento}/version', [DocumentoController::class, 'storeVersion'])->name('obras.documentos.version');
    Route::get('obras/{obra}/documentos/{documento}/descargar', [DocumentoController::class, 'descargar'])->name('obras.documentos.descargar');
    Route::get('obras/{obra}/documentos/{documento}/preview', [DocumentoController::class, 'preview'])->name('obras.documentos.preview');
    Route::delete('obras/{obra}/documentos/{documento}', [DocumentoController::class, 'destroy'])->name('obras.documentos.destroy');

    // Cuaderno de Obra Digital — selector global (sidebar)
    Route::get('cuaderno', CuadernoSelectorController::class)->name('cuaderno.selector');

    // Cuaderno de Obra Digital — por obra
    Route::get('obras/{obra}/cuaderno', [AsientoCuadernoController::class, 'index'])->name('obras.cuaderno.index');
    Route::post('obras/{obra}/cuaderno', [AsientoCuadernoController::class, 'store'])->name('obras.cuaderno.store');
    Route::delete('obras/{obra}/cuaderno/{asiento}', [AsientoCuadernoController::class, 'destroy'])->name('obras.cuaderno.destroy');
    Route::get('obras/{obra}/cuaderno/{asiento}/descargar', [AsientoCuadernoController::class, 'descargar'])->name('obras.cuaderno.descargar');
    Route::get('obras/{obra}/cuaderno/{asiento}/preview', [AsientoCuadernoController::class, 'preview'])->name('obras.cuaderno.preview');

    // Notificaciones
    Route::get('notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('notificaciones/{notificacion}/leida', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leida');
    Route::post('notificaciones/marcar-todas', [NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.marcar-todas');
    Route::delete('notificaciones/{notificacion}', [NotificacionController::class, 'eliminar'])->name('notificaciones.eliminar');

    // Calendario — selector global (sidebar)
    Route::get('calendario', CalendarioSelectorController::class)->name('calendario.selector');

    // Calendario por obra
    Route::get('obras/{obra}/calendario', [EventoCalendarioController::class, 'index'])->name('obras.calendario.index');
    Route::post('obras/{obra}/calendario', [EventoCalendarioController::class, 'store'])->name('obras.calendario.store');
    Route::patch('obras/{obra}/calendario/{evento}', [EventoCalendarioController::class, 'update'])->name('obras.calendario.update');
    Route::delete('obras/{obra}/calendario/{evento}', [EventoCalendarioController::class, 'destroy'])->name('obras.calendario.destroy');

    // Vista global de equipo (lista todas las personas + invitaciones pendientes)
    Route::get('equipo', EquipoGlobalController::class)->name('equipo.index');

    // Equipo de una obra
    Route::post('obras/{obra}/equipo/invitar', [EquipoObraController::class, 'invitar'])->name('obras.equipo.invitar');
    Route::patch('obras/{obra}/equipo/{usuario}', [EquipoObraController::class, 'cambiarRol'])->name('obras.equipo.cambiar-rol');
    Route::delete('obras/{obra}/equipo/{usuario}', [EquipoObraController::class, 'remover'])->name('obras.equipo.remover');
    Route::delete('obras/{obra}/invitaciones/{invitacion}', [EquipoObraController::class, 'cancelarInvitacion'])->name('obras.invitaciones.cancelar');
    Route::post('obras/{obra}/invitaciones/{invitacion}/reenviar', [EquipoObraController::class, 'reenviarInvitacion'])->name('obras.invitaciones.reenviar');

    // Aceptar invitación cuando ya hay sesión con el mismo correo
    Route::post('invitaciones/{token}/aceptar', [InvitacionController::class, 'aceptarAuth'])->name('invitaciones.aceptar');

    // Certificados
    Route::get('certificados/{certificado}/pdf', [CertificadoController::class, 'pdf'])->name('certificados.pdf');
    Route::get('certificados/{certificado}/preview', [CertificadoController::class, 'preview'])->name('certificados.preview');
    Route::post('certificados/{certificado}/revocar', [CertificadoController::class, 'revocar'])->name('certificados.revocar');
    Route::resource('certificados', CertificadoController::class)
        ->except(['edit', 'update']);
});

require __DIR__.'/settings.php';
