<?php

declare(strict_types=1);

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Vercel entry point untuk Laravel
|--------------------------------------------------------------------------
|
| File ini menggantikan peran web server (Nginx/Apache) di Vercel:
| - menyajikan aset statis dari folder public/
| - menyiapkan direktori writable di /tmp
| - menjalankan migrate + seed otomatis untuk SQLite sementara
| - meneruskan request ke Laravel.
|
*/

$projectRoot = dirname(__DIR__);
$publicRoot = realpath($projectRoot.'/public');

// Di Vercel hanya /tmp yang bisa ditulis.
$writableDirectories = [
    '/tmp/views',
    '/tmp/storage/app/private',
    '/tmp/storage/app/public',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
];

foreach ($writableDirectories as $directory) {
    if (! is_dir($directory)) {
        @mkdir($directory, 0777, true);
    }
}

/*
| Nilai default yang aman untuk serverless. Environment variable yang
| benar-benar di-set di Vercel Dashboard tidak akan ditimpa.
*/
$defaults = [
    // Demo key. Ganti lewat Environment Variable APP_KEY di Vercel untuk penggunaan nyata.
    'APP_KEY' => 'base64:zEVW+oggDlxyM8/PursFx3saTP134xjklQthL9Zdg6U=',
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'APP_MAINTENANCE_DRIVER' => 'file',
    'APP_LOCALE' => 'id',
    'APP_FALLBACK_LOCALE' => 'id',
    'APP_FAKER_LOCALE' => 'id_ID',
    'APP_TIMEZONE' => 'Asia/Jakarta',
    'LOG_CHANNEL' => 'stderr',
    'SESSION_DRIVER' => 'cookie',
    'CACHE_STORE' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'FILESYSTEM_DISK' => 'local',
    'UPLOAD_DRIVER' => 'database',
    'VIEW_COMPILED_PATH' => '/tmp/views',
    'APP_CONFIG_CACHE' => '/tmp/config.php',
    'APP_EVENTS_CACHE' => '/tmp/events.php',
    'APP_PACKAGES_CACHE' => '/tmp/packages.php',
    'APP_ROUTES_CACHE' => '/tmp/routes.php',
    'APP_SERVICES_CACHE' => '/tmp/services.php',
];

$trackedKeys = array_values(array_unique(array_merge(
    array_keys($defaults),
    ['APP_URL', 'DB_CONNECTION', 'DB_DATABASE', 'DB_URL']
)));

// Catat env asli SEBELUM Laravel memuat file .env (jika ada).
$hasProcessEnv = [];
foreach ($trackedKeys as $key) {
    $hasProcessEnv[$key] = getenv($key) !== false;
}

/*
| -------------------------------------------------------------------------
| Serve static assets (CSS/JS dari Vite, gambar, favicon, dll.)
| -------------------------------------------------------------------------
*/
$mimeTypes = [
    'css' => 'text/css; charset=utf-8',
    'js' => 'application/javascript; charset=utf-8',
    'mjs' => 'application/javascript; charset=utf-8',
    'json' => 'application/json; charset=utf-8',
    'map' => 'application/json; charset=utf-8',
    'txt' => 'text/plain; charset=utf-8',
    'svg' => 'image/svg+xml',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'webp' => 'image/webp',
    'ico' => 'image/x-icon',
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf' => 'font/ttf',
];

$requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

if ($publicRoot !== false && $requestPath !== '/' && ! str_starts_with($requestPath, '/api/')) {
    $candidate = realpath($publicRoot.'/'.ltrim($requestPath, '/'));
    $publicPrefix = $publicRoot.DIRECTORY_SEPARATOR;

    if ($candidate !== false
        && is_file($candidate)
        && str_starts_with($candidate, $publicPrefix)) {
        $extension = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));

        if (isset($mimeTypes[$extension])) {
            header('Content-Type: '.$mimeTypes[$extension]);
            header('Content-Length: '.(string) filesize($candidate));
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: '.(
                str_starts_with($requestPath, '/build/')
                    ? 'public, max-age=31536000, immutable'
                    : 'public, max-age=3600'
            ));

            readfile($candidate);
            exit;
        }
    }
}

/*
| -------------------------------------------------------------------------
| Bootstrap Laravel, lalu terapkan default serverless
| -------------------------------------------------------------------------
*/
require $projectRoot.'/vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once $projectRoot.'/bootstrap/app.php';

foreach ($defaults as $key => $value) {
    if (! $hasProcessEnv[$key]) {
        putenv($key.'='.$value);
        $_ENV[$key] = $_SERVER[$key] = $value;
    }
}

// APP_URL mengikuti host Vercel bila tidak diset eksplisit.
if (! $hasProcessEnv['APP_URL']) {
    $host = $_SERVER['VERCEL_URL'] ?? $_SERVER['HTTP_HOST'] ?? '';
    if (is_string($host) && $host !== '') {
        if (! str_starts_with($host, 'http')) {
            $host = 'https://'.$host;
        }
        putenv('APP_URL='.$host);
        $_ENV['APP_URL'] = $_SERVER['APP_URL'] = $host;
    }
}

// Tanpa DB_URL, pakai SQLite sementara di /tmp.
if (! $hasProcessEnv['DB_URL']) {
    if (! $hasProcessEnv['DB_CONNECTION']) {
        putenv('DB_CONNECTION=sqlite');
        $_ENV['DB_CONNECTION'] = $_SERVER['DB_CONNECTION'] = 'sqlite';
    }

    if (! $hasProcessEnv['DB_DATABASE']) {
        putenv('DB_DATABASE=/tmp/database.sqlite');
        $_ENV['DB_DATABASE'] = $_SERVER['DB_DATABASE'] = '/tmp/database.sqlite';
    }
}

$dbConnection = getenv('DB_CONNECTION') ?: 'sqlite';
$dbUrl = getenv('DB_URL') ?: '';
$database = getenv('DB_DATABASE') ?: '/tmp/database.sqlite';

if ($dbConnection === 'sqlite' && $dbUrl === '' && $database !== ':memory:' && ! file_exists($database)) {
    touch($database);

    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->call('migrate', ['--force' => true, '--no-interaction' => true]);
    $kernel->call('db:seed', ['--force' => true, '--no-interaction' => true]);
}

if (file_exists($maintenance = $projectRoot.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

$app->handleRequest(\Illuminate\Http\Request::capture());
