<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

/*
| Migration + seed yang aman untuk fase build di Vercel.
|
| Bila database belum siap (DB_URL/DB_HOST placeholder, host tidak bisa
| di-resolve, koneksi ditolak, dll.), script sengaja keluar dengan status 0
| agar build tidak gagal. api/index.php akan mencoba lagi untuk SQLite
| saat cold start.
*/

require __DIR__.'/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require __DIR__.'/../bootstrap/app.php';

/** @var \Illuminate\Foundation\Console\Kernel $kernel */
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$connection = (string) config('database.default');
$ready = true;

try {
    if ($connection === 'sqlite') {
        $path = (string) config('database.connections.sqlite.database');

        if ($path === '' || str_contains($path, '...')) {
            $ready = false;
        } elseif (! file_exists($path)) {
            @touch($path);
        }
    } else {
        DB::connection()->getPdo();
    }
} catch (Throwable $e) {
    fwrite(STDERR, '[vercel-db] Database belum siap, migrate dilewati: '.$e->getMessage().PHP_EOL);
    $ready = false;
}

if (! $ready) {
    exit(0);
}

try {
    $kernel->call('migrate', ['--force' => true, '--no-interaction' => true]);
    $kernel->call('db:seed', ['--force' => true, '--no-interaction' => true]);
    fwrite(STDOUT, $kernel->output());
} catch (Throwable $e) {
    fwrite(STDERR, '[vercel-db] migrate/seed gagal, build tetap dilanjutkan: '.$e->getMessage().PHP_EOL);
}

exit(0);
