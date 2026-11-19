# Deploy Laravel `badminton-app` ke Vercel

## Ringkasan perubahan

- `vercel.json` → memakai runtime PHP `vercel-php@0.8.0` (PHP 8.4).
- `api/index.php` → entry point serverless, melayani aset `public/`, menyiapkan folder `/tmp`, dan auto `migrate --seed` untuk SQLite sementara.
- `composer.json` → script `vercel` menjalankan `npm install`, `npm run build`, `migrate --force`, dan `db:seed --force` saat build.
- Session/cache/queue diubah ke `cookie` / `array` / `sync` agar aman di filesystem read-only Vercel.
- Upload bukti transfer disimpan sebagai data URI di database (`UPLOAD_DRIVER=database`) supaya tidak bergantung pada `storage/` yang tidak persisten.

> `vercel-php` adalah runtime komunitas. Untuk produksi skala besar, Vercel menyarankan container, tetapi untuk UTS/demo ini konfigurasi di bawah cukup.

---

## 1. Siapkan database eksternal gratis (disarankan)

Contoh: **Neon** (`https://neon.tech`) atau **Supabase** (`https://supabase.com`) → buat project Postgres → salin connection string.

Contoh bentuk `DB_URL`:

```text
postgresql://USER:PASSWORD@HOST/db_name?sslmode=require
```

Untuk MySQL:

```text
mysql://USER:PASSWORD@HOST:3306/db_name
```

Jika `DB_URL` tidak diisi, aplikasi tetap jalan memakai SQLite di `/tmp/database.sqlite`, tetapi **data akan hilang ketika function cold start**. Untuk demo singkat ini boleh; untuk UTS yang melibatkan register/booking, pakai DB eksternal.

---

## 2. Set environment variable di Vercel

Buka **Vercel Dashboard → Project → Settings → Environment Variables**, lalu tambahkan nilai dari `.env.vercel.example`.

Paling penting:

| Key | Contoh | Catatan |
| --- | --- | --- |
| `APP_KEY` | `base64:...` | Generate dengan `php artisan key:generate`. Ganti key demo jika mau. |
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | |
| `DB_CONNECTION` | `pgsql` | atau `mysql` |
| `DB_URL` | connection string | Jangan di-commit ke GitHub |
| `SESSION_DRIVER` | `cookie` | |
| `CACHE_STORE` | `array` | |
| `QUEUE_CONNECTION` | `sync` | |
| `UPLOAD_DRIVER` | `database` | |

`APP_URL` boleh dikosongkan karena `api/index.php` akan mengisi dari host Vercel secara otomatis.

---

## 3. Deploy via terminal (Vercel CLI)

```powershell
cd "D:\skripsi kating\badminton-app"

# Install CLI (sekali saja)
npm i -g vercel

# Login
vercel login

# Hubungkan folder ini dengan project Vercel
vercel link

# Tambahkan environment variable (ulang untuk key lain)
vercel env add APP_KEY production
vercel env add DB_URL production
vercel env add DB_CONNECTION production

# Deploy production
vercel --prod
```

Deployment selesai → buka URL yang ditampilkan Vercel.

---

## 4. Alternatif: push ke GitHub lalu deploy

```powershell
cd "D:\skripsi kating\badminton-app"

git init
git add .
git commit -m "Konfigurasi deploy Laravel ke Vercel"
git branch -M main
git remote add origin https://github.com/USERNAME/badminton-app.git
git push -u origin main

vercel --prod
```

Saat import repository di Vercel, pilih **Framework Preset: Other**. Environment variable tetap ditambahkan seperti langkah 2. File `.env` tidak ikut ter-commit karena sudah ada di `.gitignore`.

---

## 5. Akun demo setelah seed

| Role | Username / Email | Password |
| --- | --- | --- |
| Admin | `admin` / `admin@gormetro.com` | `password123` |
| Pelanggan | `pelanggan` / `user@gmail.com` | `password123` |

---

## 6. Cek lokal sebelum deploy

```powershell
cd "D:\skripsi kating\badminton-app"
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Buka `http://localhost:8000`.

---

## Troubleshooting singkat

- **500 / "No application encryption key"** → `APP_KEY` belum ada di Vercel.
- **Tabel tidak ditemukan** → cek `DB_URL`/`DB_CONNECTION`, lalu pastikan script `vercel` di `composer.json` tidak dihapus. Atau jalankan sekali: `vercel env pull .env.vercel.local` lalu `php artisan migrate --force`.
- **CSS/JS 404** → pastikan `npm run build` berhasil dan `public/build/manifest.json` ikut ter-upload. Cek log build Vercel.
- **Function timeout saat cold start** → `maxDuration` sudah diset 60 detik di `vercel.json`; bisa dinaikkan di dashboard jika pakai plan berbayar.
