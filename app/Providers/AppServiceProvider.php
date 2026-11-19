<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // Vercel mengakhiri koneksi TLS di reverse proxy, sehingga request yang
        // diterima PHP terlihat HTTP. Paksa semua URL yang dihasilkan Laravel
        // (route(), url(), asset(), redirect(), dll.) memakai skema https.
        $forwardedProto = strtolower(trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0]));
        $https = $forwardedProto === 'https'
            || in_array(strtolower((string) ($_SERVER['HTTPS'] ?? '')), ['on', '1'], true)
            || (getenv('VERCEL') !== false && getenv('VERCEL') !== '')
            || (bool) env('VERCEL')
            || app()->environment('production')
            || str_starts_with((string) config('app.url'), 'https://');

        if ($https) {
            URL::forceScheme('https');
        }
    }
}
