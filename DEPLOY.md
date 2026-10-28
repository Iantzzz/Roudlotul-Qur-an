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
