<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use League\Flysystem\Filesystem;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNAdapter;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registrarListenerDeLogin();
        $this->registrarDiscoBunny();
    }

    /**
     * Registra el driver `bunny` para Storage::disk(), usando la API nativa
     * de Bunny.net Edge Storage. Cualquier disco con driver=bunny puede usar
     * los helpers normales de Laravel (put, get, delete, readStream, etc.).
     */
    protected function registrarDiscoBunny(): void
    {
        Storage::extend('bunny', function ($app, array $config): FilesystemAdapter {
            $client = new BunnyCDNClient(
                $config['storage_zone'],
                $config['api_key'],
                $config['region'] ?? '',
            );

            $adapter = new BunnyCDNAdapter($client);

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config,
            );
        });
    }

    /**
     * Actualiza last_login_at / last_login_ip cada vez que un usuario inicia sesión.
     */
    protected function registrarListenerDeLogin(): void
    {
        Event::listen(Login::class, function (Login $event) {
            $event->user->forceFill([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ])->saveQuietly();
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
