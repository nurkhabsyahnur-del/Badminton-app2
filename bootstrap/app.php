<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // IP proxy Vercel tidak tetap, jadi proxy dipercaya di lingkungan
        // produksi/Vercel. Header X-Forwarded-* dipakai untuk mendeteksi
        // skema asli (https) dan host asli sebelum melewati reverse proxy.
        if (env('APP_ENV') === 'production'
            || (getenv('VERCEL') !== false && getenv('VERCEL') !== '')
            || (bool) env('VERCEL')) {
            $middleware->trustProxies(
                at: '*',
                headers: Request::HEADER_X_FORWARDED_FOR
                    | Request::HEADER_X_FORWARDED_HOST
                    | Request::HEADER_X_FORWARDED_PORT
                    | Request::HEADER_X_FORWARDED_PROTO,
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

// Di Vercel atau bila LARAVEL_STORAGE_PATH diset, alihkan direktori storage ke /tmp
if (getenv('VERCEL') !== false || env('VERCEL') || env('LARAVEL_STORAGE_PATH')) {
    $storagePath = env('LARAVEL_STORAGE_PATH', '/tmp/storage');
    $app->useStoragePath($storagePath);
}

return $app;
