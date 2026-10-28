# Panduan Online Deployment

Repo ini siap di-deploy. Ada dua jalur; pilih sesuai anggaran dan kebutuhan
domain resmi lanjutan (Tahap 2). Jalankan semua perintah di mesin lokal dengan
`cmd /c "php artisan ..."` bila memakai PowerShell.

## Jalur A — Laravel Cloud (paling mudah, otomatis tiap push)

Laravel Cloud (https://cloud.laravel.com) adalah hosting resmi tim Laravel yang
terhubung langsung ke repo GitHub ini. Setiap `git push` ke `main` otomatis
mendeploy versi terbaru — tidak perlu menyentuh server.

Prasyarat:

- Akun GitHub (yang berakses ke repo `Iantzzz/Roudlotul-Qur-an`).
- Kartu kredit/debit untuk pay-as-you-go; cek halaman harga di
  https://laravel.com/cloud — compute "Flex" bisa scale-to-zero sehingga biaya
  situs kecil sangat rendah.

Langkah (mengikuti quickstart resmi):

1. Buka dashboard Laravel Cloud → **+ New Application** → **Connect to GitHub**.
2. Otorisasi dan pastikan GitHub App Laravel Cloud punya akses ke repo ini.
3. Pilih repository `Iantzzz/Roudlotul-Qur-an`, beri nama aplikasi, pilih
   region **Asia Pacific (Singapore) `ap-southeast-1`** (terdekat dari Indonesia).
4. Di pengaturan environment: runtime **PHP**, versi **8.3** (repo menuntut
   `php:^8.3`).
5. Isi **build commands**:
   ```
   composer install --no-dev
   npm ci --audit false
   npm run build
   php artisan optimize
   ```
6. Isi **deploy command**:
   ```
   php artisan migrate --force
   ```
7. Klik **Add database** di canvas infrastruktur → **Laravel MySQL** (region
   sama). Cloud otomatis meng-inject `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`,
   `DB_DATABASE`.
8. Tambah **environment variables** minimal ini (jangan lewat chat!):
   | Variabel | Nilai |
   | --- | --- |
   | `APP_ENV` | `production` |
   | `APP_DEBUG` | `false` |
   | `APP_KEY` | hasil perintah `php artisan key:generate --show` di terminal lokal Anda — salin langsung ke dashboard, jangan dikirim ke mana pun |
   | `APP_URL` | URL sementara cloud atau domain resmi |
   | `CACHE_STORE` | `database` |
   | `SESSION_DRIVER` | `database` |
9. Klik **Deploy**. Situs hidup di domain sementara ala
   `nama-app.laravel.cloud`.
10. Domain resmi (opsional, kapan pun): map domain hasil beli registrar ke
    aplikasi di menu domains, setelah itu update `APP_URL`.

Membuat akun admin panel (`/admin`, Filament):

- Jangan buat user lewat chat/password yang dikirim ke mana pun.
- Buka **Console** aplikasi di dashboard Cloud, lalu jalankan:
  ```
  php artisan make:filament-user
  ```
  dan isi nama, email, dan password secara interaktif.

Checklist setelah live:

- [ ] Halaman `/`, `/profil`, `/kegiatan`, `/galeri`, `/berita`, `/pendaftaran`, `/kontak` merespons 200.
- [ ] `/admin` bisa login dengan akun yang dibuat di langkah di atas.
- [ ] `APP_DEBUG=false` (detail error tidak terlihat oleh pengunjung).
- [ ] Semua placeholder `[Sementara]` masih utuh sampai data resmi dari pengurus tersedia (lihat `CHECKLIST_KONTEN.md`).

## Jalur B — Hosting cPanel lokal (Niagahoster, dsb.)

Situs ini jalan di shared hosting selama **PHP ≥ 8.3**. Panduan:

1. Upload isi repo (zip atau `git clone` via SSH hosting).
2. `composer install --no-dev -o` (di terminal hosting/SSH).
3. Salin `.env.example` ke `.env`, lalu isi:
   - `APP_ENV=production`, `APP_DEBUG=false`,
   - `DB_CONNECTION=mysql` + detail database hosting,
   - `APP_URL=https://domain-anda.tld`.
4. `php artisan key:generate` dan `php artisan migrate --force`.
5. `php artisan storage:link` (sekali, sesudah live, bukan di deploy script).
6. `php artisan optimize`.
7. Arahkan DocumentRoot/subdomain ke folder `public/`.

Catatan khusus: session/cache memakai driver `database` (default repo), jadi
konfigurasi MySQL wajib benar sebelum menyajikan halaman.

## Jalur C — Vercel (runtime PHP komunitas `vercel-php`)

Vercel tidak menjalankan PHP secara bawaan (build error
`STATIC_BUILD_NO_OUT_DIR` = Vercel salah menganggap repo ini situs statis
Vite). Repo ini sudah disiapkan dengan `vercel.json` pola resmi dari
https://github.com/vercel-community/php:

- `builds`: satu build — `api/index.php` memakai runtime `vercel-php@0.7.4`
  (PHP 8.3); `routes` catch-all mengarahkan seluruh permintaan ke sana.
- `api/index.php` bertindak sebagai sistem asset + fallback Laravel:
  - Asset yang masuk daftar (allowlist) dilayani langsung dari `public/`:
    `/css/site.css`, `/css/filament/**`, `/js/site.js`, `/js/filament/**`,
    `/images/**`, `/fonts/filament/**`, `favicon.ico`, `favicon-32x32.png`,
    `apple-touch-icon.png`, dan `robots.txt` — dengan Content-Type yang
    benar, ETag/If-None-Match (304), nosniff, Cache-Control publik, dan
    tanpa cookie.
  - Path di bawah prefix asset (`/css|js|images|fonts/…`) yang tidak masuk
    daftar dijawab 404 badan kosong (termasuk traversal `..`, backslash,
    byte NUL, double-encoding, dan skrip PHP) — tidak pernah diteruskan ke
    Laravel.
  - Permintaan lain (halaman, POST formulir) diteruskan ke Laravel dengan
    metode HTTP apa pun; header HTTPS dari proxy loopback dipercayai agar
    URL asset memakai https.
  - Perilaku ini dijaga oleh `tests/Feature/VercelAssetsTest.php`.
- `env`: `APP_ENV=production`, `APP_DEBUG=false`, `LOG_CHANNEL=stderr`,
  `SESSION_DRIVER=cookie`, `CACHE_STORE=array`, `VIEW_COMPILED_PATH=/tmp/views`
  (filesystem serverless read-only; /tmp bersifat sementara).
- `.vercelignore`: mengecualikan dependensi lokal, file environment rahasia,
  cache konfigurasi lokal, log, `tests`, dan arsip zip.

Pengaturan project Vercel:

1. **Root Directory**: root repository, tempat `vercel.json` berada.
2. **Framework Preset**: **Other**; matikan override **Build Command** dan
   **Output Directory**. Jangan isi `dist` atau `public/build`: konfigurasi
   `builds` memakai runtime PHP dan asset statis di `public/`, bukan hasil
   build situs Vite. Halaman publik memakai `public/css/site.css` dan
   `public/js/site.js` langsung.
3. **Node.js Version**: **22.x**, sesuai dukungan runtime komunitas.
4. Isi environment variables di bawah, lalu deploy commit terbaru.

Keterbatasan yang perlu diketahui sejak awal:

1. Database tidak bawaan: panel admin (`/admin`) baru hidup setelah database
   dan env admin diisi (lihat bagian "Mengaktifkan panel admin" di bawah).
2. Session memakai driver `cookie` (serverless tidak punya storage persist
   antar instance).

Yang **harus** diisi di dashboard Vercel (Environment Variables, Production),
jangan lewat chat/file repo:

| Variabel | Nilai |
| --- | --- |
| `APP_KEY` | jalankan `cmd /c "php artisan key:generate --show"` di terminal lokal, salin langsung ke dashboard |
| `APP_URL` | URL HTTPS yang ditampilkan Vercel pada menu Domains (atau domain akhir) |

Tanpa `APP_KEY`, halaman menghasilkan HTTP 500 (`MissingAppKeyException`).
Perintah `--show` hanya menampilkan key, tidak mengganti `.env` lokal.
Pilih environment **Preview** juga bila ingin menguji preview deployment.

Setelah keduanya diisi: Deployments → **Redeploy**. Perubahan env hanya berlaku
setelah redeploy. Versi PHP lain bisa dipilih (`0.8.0` = 8.4, `0.9.0` = 8.5)
dengan mengubah nomor runtime di `vercel.json`.

### Mengaktifkan panel admin (`/admin`) di Vercel

Repo sudah menata agar Filament jalan di deployment Vercel:

1. Asset publik Filament (`public/css/filament`, `public/js/filament`,
   `public/fonts/filament`) ikut ter-commit dan dilayani langsung oleh
   `api/index.php` (allowlist asset). Bila Filament/composer diperbarui,
   jalankan ulang `php artisan filament:assets` lalu commit perubahannya.
2. Session memakai driver `cookie` (serverless tidak punya storage persist
   antar instance).
3. Script composer `vercel` dijalankan otomatis runtime saat build:
   `migrate --force` diikuti seeder `AdminUserSeeder` (keduanya aman bila
   database/env belum tersedia).

Setup database + akun admin dari dashboard Vercel:

1. Buat database dari **Vercel Marketplace** (mis. Neon/Postgres tier gratis).
2. Isi Environment Variables (Production):
   - `APP_KEY` — jalankan `php artisan key:generate --show` di terminal lokal,
     salin langsung ke dashboard (jangan dikirim ke chat).
   - `APP_URL` — `https://<nama-project>.vercel.app`.
   - `DB_CONNECTION` — `pgsql`.
   - `DB_URL` — `${DATABASE_URL}` (nama env yang disuntik marketplace bisa
     berbeda; pakai nama yang muncul di halaman database).
   - `ADMIN_NAME`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` — kredensial login admin.
     Password di-hash saat build dan disinkronkan dari env pada tiap build.
3. Deployments → **Redeploy**, lalu buka `/admin/login` dan login.

Agar situs benar-benar bisa diakses publik: **Settings → Deployment
Protection** → nonaktifkan proteksi *Vercel Authentication*; tanpa itu semua
URL meminta login akun Vercel.

Batasan yang tetap berlaku di Vercel: tidak ada file storage persist untuk
unggahan, cache/scheduler tidak berjalan. Bila kebutuhannya bertambah,
pindah ke Jalur A (Laravel Cloud) atau Jalur B (cPanel).
