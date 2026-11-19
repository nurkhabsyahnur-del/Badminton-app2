<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Validasi konfigurasi database untuk lingkungan serverless (Vercel).
 *
 * Nilai kosong/placeholder seperti "..." tidak boleh diteruskan ke PDO,
 * karena akan memicu error:
 * SQLSTATE[08006] could not translate host name "..." to address.
 */
class VercelDatabase
{
    /**
     * Anggap kosong/placeholder sebagai "belum dikonfigurasi".
     *
     * @param  mixed  $value
     */
    public static function isPlaceholder($value): bool
    {
        if (! is_string($value) && $value !== null) {
            return true;
        }

        $value = strtolower(trim((string) $value));

        if ($value === '') {
            return true;
        }

        foreach (['...', '…', 'changeme', 'your-', 'your_', '<', '>', 'xxx'] as $needle) {
            if (str_contains($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Host lokal tidak tersedia di Vercel.
     *
     * @param  mixed  $value
     */
    public static function isLocalHost($value): bool
    {
        if (! is_string($value)) {
            return true;
        }

        return in_array(
            strtolower(trim($value)),
            ['localhost', '127.0.0.1', '::1', '[::1]', '0.0.0.0'],
            true
        );
    }

    /**
     * Kembalikan DB_URL hanya bila benar-benar bisa dipakai.
     *
     * @param  mixed  $value
     */
    public static function validUrl($value): ?string
    {
        if (self::isPlaceholder($value)) {
            return null;
        }

        $url = trim((string) $value);
        $parts = parse_url($url);

        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        if (! in_array(strtolower($parts['scheme']), ['postgres', 'postgresql', 'mysql', 'mariadb'], true)) {
            return null;
        }

        if (self::isPlaceholder($parts['host']) || self::isLocalHost($parts['host'])) {
            return null;
        }

        return $url;
    }

    /**
     * Kembalikan DB_HOST hanya bila benar-benar bisa di-resolve.
     *
     * @param  mixed  $value
     */
    public static function validHost($value): ?string
    {
        if (self::isPlaceholder($value)) {
            return null;
        }

        $host = trim((string) $value);

        if (self::isLocalHost($host)) {
            return null;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) === false
            && filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
            return null;
        }

        return $host;
    }

    /**
     * @param  mixed  $value
     */
    public static function validPort($value): ?int
    {
        if (self::isPlaceholder($value) || ! is_numeric($value)) {
            return null;
        }

        $port = (int) $value;

        return $port > 0 && $port <= 65535 ? $port : null;
    }

    /**
     * @param  mixed  $value
     */
    public static function validDatabaseName($value): ?string
    {
        if (self::isPlaceholder($value)) {
            return null;
        }

        $name = trim((string) $value);

        return $name === '' ? null : $name;
    }

    /**
     * @param  mixed  $connection
     */
    public static function shouldUseSqlite($connection, ?string $url, ?string $host, bool $onVercel): bool
    {
        $connection = (is_string($connection) && trim($connection) !== '')
            ? trim($connection)
            : 'sqlite';

        if ($connection === 'sqlite') {
            return true;
        }

        if (! $onVercel) {
            return false;
        }

        // Di Vercel, tanpa URL/host yang valid jangan pernah pakai driver eksternal.
        return $url === null && $host === null;
    }

    /**
     * Path SQLite yang aman untuk runtime read-only.
     *
     * @param  mixed  $configured
     */
    public static function sqliteDatabase($configured, bool $onVercel): string
    {
        if (self::isPlaceholder($configured)) {
            return $onVercel ? '/tmp/database.sqlite' : database_path('database.sqlite');
        }

        $path = trim((string) $configured);

        if ($onVercel
            && ! str_starts_with($path, '/tmp/')
            && ! str_ends_with(strtolower($path), '.sqlite')) {
            return '/tmp/database.sqlite';
        }

        return $path;
    }
}
