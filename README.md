# Situs Publik Roudlotul Qur'an

Situs web publik untuk asrama Roudlotul Qur'an, dibangun dengan Laravel dan
view Blade murni (CSS/JS statis tanpa proses build). Panel `/admin` berbasis
Filament ada di dalam repo dan **tidak diubah** pada tahap ini.

> **Status konten:** seluruh data resmi (alamat, kontak, jadwal, biaya,
> nama program, foto dokumentasi) **belum tersedia** dan masih memakai
> placeholder `[Sementara]` serta badge `x-temp-badge`. Jangan menuliskan
> angka, tanggal, nama, atau pernyataan resmi yang belum dikonfirmasi
> pengurus. Rincian kebutuhan konten dicatat di
> [`CHECKLIST_KONTEN.md`](CHECKLIST_KONTEN.md).

## Halaman publik

| Rute | Keterangan |
| --- | --- |
| `/` | Beranda: hero, fondasi, pilar, kegiatan, berita terbaru, galeri |
| `/profil` | Profil singkat, sejarah, visi & misi, nilai-nilai (penanda sementara) |
| `/kegiatan` | Kelompok program: ibadah, pendidikan, karakter, kemandirian |
| `/galeri` | Grid foto + pratinjau lightbox (`<dialog>`) |
| `/berita` | Daftar berita & pengumuman dengan filter kategori `?kategori=` |
| `/berita/{slug}` | Detail artikel + berita terkait |
| `/pendaftaran` | Alur, persyaratan, jadwal, biaya, FAQ (placeholder) |
| `/kontak` | Detail kontak, formulir tampilan (nonaktif), placeholder peta |
| halaman tidak ditemukan | 404 kustom (`resources/views/errors/404.blade.php`) |

Konten berita dan galeri saat ini berasal dari data contoh di
`app/Http/Controllers/PublicPageController.php` (bukan database).

## Teknologi

- Laravel 12 dengan PHP 8.3.
- CSS dan JS publik statis: `public/css/site.css` dan `public/js/site.js`.
- Filament 3 untuk panel `/admin`.
- Aksesibilitas: skip link, `aria-current` untuk navigasi aktif, lightbox
  berbasis `<dialog>`, dukungan `prefers-reduced-motion`, serta tampilan
  tetap utuh tanpa JavaScript.

## Menjalankan lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve   # http://127.0.0.1:8000
```

Tahap ini tidak memerlukan database untuk tampilan publik: konten berita dan
galeri berasal dari controller, bukan tabel. `php artisan migrate` hanya
menyiapkan tabel bawaan Laravel.

## Pengujian

```bash
php artisan test
```

`tests/Feature/PublicPagesTest.php` mencakup: seluruh halaman publik merespons
200, filter kategori berita berfungsi, slug tidak dikenal mengembalikan 404,
dan halaman 404 kustom tampil.

## Struktur penting

- `app/Http/Controllers/PublicPageController.php` — sumber data sementara
  untuk seluruh halaman publik.
- `config/site.php` — brand, navigasi, kontak placeholder, dan mapping gambar
  beserta atribusi Unsplash.
- `resources/views` — layout `layouts.public`, partial navbar/footer, dan
  komponen (`x-page-hero`, `x-site-image`, `x-news-card`, dll.).
- `public/css/site.css`, `public/js/site.js` — gaya serta interaksi nav,
  header, lightbox, dan reveal.
- `CHECKLIST_KONTEN.md` — daftar data resmi yang harus disiapkan pengurus
  sebelum placeholder diganti.

## Pedoman pengisian data resmi

1. Ganti placeholder dan teks sementara di `config/site.php` serta view dengan
   data resmi dari pengurus — satu per satu, jangan dikarang sendiri.
2. Ganti gambar sementara di `public/images/site` (semua masih foto Unsplash;
   atribusinya tercatat di `config/site.php`).
3. Setelah data resmi satu halaman lengkap, hapus badge `x-temp-badge` pada
   halaman tersebut.

## Catatan git

- Repo: https://github.com/Iantzzz/Roudlotul-Qur-an.git (branch `main`).
- `.env`, `vendor`, `node_modules`, dan arsip `roudlotul-quran.zip` tidak
  ikut ter-push.
