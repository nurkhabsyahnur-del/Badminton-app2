# Deploy Laravel `badminton-app` ke Vercel

## Ringkasan perubahan

- `vercel.json` → memakai builder eksplisit: `vercel-php@0.8.0` untuk `api/index.php` dan `@vercel/static` **hanya untuk aset** (`public/build/**`, `public/images/**`, `favicon.ico`, `robots.txt`). `public/index.php` dan `.htaccess` sengaja **tidak** ikut ter-build sebagai statis — ini penyebab URL mengunduh file 543 B. Request lain diarahkan ke `/api/index.php`.
- `api/index.php` → entry point serverless yang akhirnya memanggil `public/index.php`, dengan fallback aset statis, folder `/tmp`, auto `migrate --seed` SQLite saat cold start, dan `header_remove('Content-Disposition')`.
- `app/Support/VercelDatabase.php` → helper terpusat untuk membuang `DB_URL`/`DB_HOST`/`DB_PORT`/`DB_DATABASE` yang kosong, placeholder `...`, atau `localhost`/`127.0.0.1` saat di Vercel. Dipakai bersama oleh config, entry point, dan script build.
- `config/database.php` → bila tidak ada URL/host eksternal yang valid, koneksi otomatis jatuh ke SQLite `/tmp/database.sqlite` (tidak mencoba `127.0.0.1:5432` maupun host `...`).
- `scripts/vercel-db.php` → migrate + seed saat build dibungkus try/catch; bila DB belum siap, build tetap lanjut (exit 0) dan akan dicoba lagi saat runtime.
- `composer.json` → script `vercel` menyiapkan file SQLite, menjalankan `npm install`, `npm run build`, lalu memanggil `scripts/vercel-db.php` (bukan `artisan migrate` langsung).
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
| `APP_URL` | `https://nama-project.vercel.app` | Wajib `https://`, ganti dengan domain produksi Anda. Jangan kosongkan/diisi `http://`. |
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | |
| `DB_CONNECTION` | `pgsql` | atau `mysql` |
| `DB_URL` | connection string | Jangan di-commit ke GitHub |
| `SESSION_DRIVER` | `cookie` | |
| `CACHE_STORE` | `array` | |
| `QUEUE_CONNECTION` | `sync` | |
| `UPLOAD_DRIVER` | `database` | |

`APP_URL` harus diisi dengan URL produksi berskema `https://`. `api/index.php` tidak mengisi nilai ini secara otomatis; jika kosong, Laravel bisa menghasilkan action form/redirect dengan skema `http://` dan browser menampilkan peringatan *"The information you're about to submit is not secure"*. Di aplikasi, `URL::forceScheme('https')` dan trusted proxy juga memastikan URL tetap HTTPS di balik reverse proxy Vercel.

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

- **`STATIC_BUILD_NO_OUT_DIR: dist`** → jangan biarkan Vercel auto-detect Vite. Pastikan `vercel.json` memakai `builds` dengan `vercel-php` + `@vercel/static`, bukan build statis `dist`.
- **500 / "No application encryption key"** → `APP_KEY` belum ada di Vercel.
- **Tabel tidak ditemukan** → cek `DB_URL`/`DB_CONNECTION`, lalu pastikan script `vercel` di `composer.json` tidak dihapus. Atau jalankan sekali: `vercel env pull .env.vercel.local` lalu `php artisan migrate --force`.
- **URL mengunduh file kecil (~543 B)** → `public/index.php` ikut ter-build sebagai `@vercel/static`. Pastikan glob static hanya mencakup aset (`public/build/**`, `public/images/**`, `favicon.ico`, `robots.txt`), bukan `public/**`.
- **CSS/JS 404** → pastikan `npm run build` berhasil dan `public/build/manifest.json` ikut ter-upload. Cek log build Vercel.
- **Error `127.0.0.1:5432`** → isi `DB_URL`/`DB_HOST` dengan host asli, atau biarkan `DB_CONNECTION=sqlite` + `DB_URL=` kosong.
- **Error `could not translate host name "..." to address`** → ada env `DB_HOST`/`DB_URL` yang masih berisi placeholder `...`. Aplikasi sekarang membuang nilai itu dan memakai SQLite `/tmp`, tetapi sebaiknya hapus/perbarui env tersebut di Dashboard agar tidak membingungkan.
- **Peringatan browser "The information you're about to submit is not secure" saat login/register** → pastikan `APP_URL` di Vercel memakai `https://` (bukan kosong/`http://`), `APP_ENV=production`, lalu **Redeploy** agar env/cache terbaru dipakai. Cek `View source` halaman login: `action` form harus diawali `https://`.
- **Function timeout saat cold start** → `maxDuration` sudah diset 60 detik di `vercel.json`; bisa dinaikkan di dashboard jika pakai plan berbayar.
