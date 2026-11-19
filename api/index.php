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

// Di Vercel hanya /tmp yang bisa ditulis.
foreach ([
    '/tmp/views',
    '/tmp/storage/app/private',
    '/tmp/storage/app/public',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
] as $directory) {
    if (! is_dir($directory)) {
        @mkdir($directory, 0777, true);
    }
}

/*
| Fallback SQLite: file di /tmp tidak ikut ter-pack dari proses build, jadi
| saat cold start kita buat lalu migrate + seed. Dijalankan di proses PHP
| terpisah supaya public/index.php tetap menjadi satu-satunya bootstrap
| untuk request HTTP (menghindari require_once bootstrap/app.php dua kali).
|
| Validasi DB_URL/DB_HOST memakai helper yang sama dengan config/database.php,
| sehingga placeholder "..." tidak pernah diteruskan ke PDO.
*/
$onVercel = getenv('VERCEL') !== false;
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
    && ! file_exists($databasePath)) {
    @touch($databasePath);

    $artisan = $projectRoot.'/artisan';

    if (is_file($artisan) && function_exists('exec')) {
        $php = escapeshellarg(PHP_BINARY);
        $artisanPath = escapeshellarg($artisan);

        exec($php.' '.$artisanPath.' migrate --force --no-interaction 2>&1');
        exec($php.' '.$artisanPath.' db:seed --force --no-interaction 2>&1');
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
