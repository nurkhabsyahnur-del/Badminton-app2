<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Vercel entry point untuk Laravel
|--------------------------------------------------------------------------
|
| Request dinamis diarahkan ke file ini oleh vercel.json. Aset statis
| (/build/*, /assets/*, /images/*, dll.) sudah diarahkan langsung ke folder
| public/ oleh @vercel/static. Fallback di bawah dipakai hanya bila file
| hasil npm run build belum terlihat oleh builder statis.
|
| PENTING: Laravel tetap diproses oleh public/index.php.
|
*/

$projectRoot = dirname(__DIR__);
$publicRoot = realpath($projectRoot.'/public');

// Helper validasi DB dipakai bersama config/database.php dan scripts/vercel-db.php.
require_once $projectRoot.'/app/Support/VercelDatabase.php';

// Di Vercel hanya /tmp yang bisa ditulis. Pastikan semua folder writable Laravel
// otomatis dibuat di /tmp sebelum aplikasi Laravel di-bootstrap.
$writableDirectories = [
    '/tmp/views',
    '/tmp/storage/app/private',
    '/tmp/storage/app/public',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($writableDirectories as $directory) {
    if (! is_dir($directory)) {
        @mkdir($directory, 0777, true);
    }
}

// Inisialisasi environment variable penting agar Laravel mengarahkan
// seluruh cache, views, logs, dan sessions ke /tmp yang writable.
$defaultEnvs = [
    'VERCEL' => '1',
    'LARAVEL_STORAGE_PATH' => '/tmp/storage',
    'VIEW_COMPILED_PATH' => '/tmp/storage/framework/views',
    'APP_SERVICES_CACHE' => '/tmp/bootstrap/cache/services.php',
    'APP_PACKAGES_CACHE' => '/tmp/bootstrap/cache/packages.php',
    'APP_CONFIG_CACHE' => '/tmp/bootstrap/cache/config.php',
    'APP_ROUTES_CACHE' => '/tmp/bootstrap/cache/routes-v7.php',
    'APP_EVENTS_CACHE' => '/tmp/bootstrap/cache/events.php',
    'LOG_CHANNEL' => getenv('LOG_CHANNEL') ?: 'stderr',
    'SESSION_DRIVER' => getenv('SESSION_DRIVER') ?: 'cookie',
    'CACHE_STORE' => getenv('CACHE_STORE') ?: 'array',
];

foreach ($defaultEnvs as $key => $value) {
    if (getenv($key) === false || getenv($key) === '') {
        putenv("{$key}={$value}");
    }
    $_ENV[$key] = $_ENV[$key] ?? $value;
    $_SERVER[$key] = $_SERVER[$key] ?? $value;
}

/*
| Fallback SQLite: file di /tmp tidak ikut ter-pack dari proses build, jadi
| saat cold start kita salin database awal (atau buat + migrate + seed).
| Dijalankan di proses PHP terpisah supaya public/index.php tetap menjadi
| satu-satunya bootstrap untuk request HTTP.
|
| Validasi DB_URL/DB_HOST memakai helper yang sama dengan config/database.php,
| sehingga placeholder "..." atau host localhost/127.0.0.1 tidak diteruskan ke PDO.
*/
$onVercel = true;
$dbUrl = \App\Support\VercelDatabase::validUrl(getenv('DB_URL') ?: null);
$dbHost = \App\Support\VercelDatabase::validHost(getenv('DB_HOST') ?: null);
$useSqlite = \App\Support\VercelDatabase::shouldUseSqlite(
    getenv('DB_CONNECTION') ?: null,
    $dbUrl,
    $dbHost,
    $onVercel
);
$databasePath = \App\Support\VercelDatabase::sqliteDatabase(
    getenv('DB_DATABASE') ?: null,
    $onVercel
);

if ($useSqlite
    && str_starts_with($databasePath, '/tmp/')
    && (! file_exists($databasePath) || filesize($databasePath) === 0)) {
    $candidates = [
        $projectRoot.'/database/database.sqlite',
        $projectRoot.'/db_badminton',
    ];
    $copied = false;
    foreach ($candidates as $candidate) {
        if (is_file($candidate) && filesize($candidate) > 0) {
            @copy($candidate, $databasePath);
            $copied = true;
            break;
        }
    }

    if (! $copied) {
        @touch($databasePath);

        $artisan = $projectRoot.'/artisan';

        if (is_file($artisan) && function_exists('exec')) {
            $php = escapeshellarg(PHP_BINARY);
            $artisanPath = escapeshellarg($artisan);

            exec($php.' '.$artisanPath.' migrate --force --no-interaction 2>&1');
            exec($php.' '.$artisanPath.' db:seed --force --no-interaction 2>&1');
        }
    }
}

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
            header('Cache-Control: public, max-age=31536000, immutable');

            readfile($candidate);
            exit;
        }
    }
}

// Pastikan tidak ada header "download" yang terbawa ke respons Laravel.
// Content-Type HTML/JSON akan ditetapkan sendiri oleh Laravel.
header_remove('Content-Disposition');

require __DIR__ . '/../public/index.php';
