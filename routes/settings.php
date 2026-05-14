<?php

use App\Http\Controllers\Settings\BrandingController;
use App\Http\Controllers\Settings\HomeController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/appearance')->name('appearance.edit');

    Route::get('settings/branding', [BrandingController::class, 'edit'])->name('branding.edit');
    Route::post('settings/branding', [BrandingController::class, 'update'])->name('branding.update');
    Route::delete('settings/branding', [BrandingController::class, 'destroy'])->name('branding.destroy');

    Route::get('settings/home', [HomeController::class, 'edit'])->name('home.edit');
    Route::post('settings/home', [HomeController::class, 'store'])->name('home.store');
    Route::patch('settings/home/orden', [HomeController::class, 'reordenar'])->name('home.reordenar');
    Route::delete('settings/home/{imagen}', [HomeController::class, 'destroy'])->name('home.destroy');
});
