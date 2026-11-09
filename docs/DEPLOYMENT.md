# Panduan Deployment — Roumlotul Qur'an (Laravel + Neon + Vercel)

Dokumen ini adalah runbook **terkontrol** untuk mengubah produksi. Setiap
tahap menunggu persetujuan eksplisit — JANGAN menjalankan perintah
perubahan tanpa izin.

Anggaran risiko inti: **seluruh data kepengurusan (jabatan, nama, urutan,
riwayat periode) harus tetap byte-identik**. Perubahan skema sifatnya
additive: tabel `management_units` baru + kolom nullable
`management_members.management_unit_id`; tidak ada kolom/baris lama yang
ditulis ulang oleh migrasi.

---

## Peta tahap (L0–L6)

| Tahap | Isi | Sifat |
| --- | --- | --- |
| L0 | Prasyarat, kredensial, identitas DB | read-only |
| L1 | Audit inventaris (read-only) | read-only |
| L2 | Backup `-Fc` + verifikasi restore-point | tulis ke file lokal saja |
| L3 | Migrasi skema + seeding admin (terkondisi) | **mengubah DB** |
| L4 | Deploy kode (Vercel build) | **mengubah kode live** |
| L5 | Verifikasi pasca-deploy | read-only |
| L6 | Rollback (bila perlu) | **mengubah DB/kode** |

Urutan yang benar: **skema dulu (L3), kode kemudian (L4)** — kode lama
tetap kompatibel dengan skema baru (kolom baru bersifat nullable dan
tidak pernah dibaca oleh kode lama), sedangkan kode baru memerlukan
skema baru.

---

## L0 — Prasyarat & identitas database

1. Variabel kredensial produksi tersedia di sesi (HANYA lokal, tidak pernah
   dikommit):
   - `DB_HOST` / `DB_PORT` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD`
     (alamat **DIRECT** Neon untuk CLI migrate, bukan pooler Kumandra).
2. PHP CLI lokal: `php artisan about` harus menunjukkan koneksi prod yang
   benar sebelum apa pun.
3. Kuncian pembeda branch: nama database test wajib berawalan `rlq_test_`.
   PHPUnit (Suite B) menegakkan lewat interlock
   `tests/Db/DbPgsqlTestCase.php` (driver pgsql + `current_database()`
   berawalan `rlq_test_`, dicek SEBELUM `RefreshDatabase`).
4. Verifikasi versi PostgreSQL (menentukan jalur FK):
   `SELECT setting FROM pg_settings WHERE name='server_version_num'`
   - `>= 150000` → composite FK `ON DELETE SET NULL (kolom)`.
   - `< 150000` → FK sederhana + trigger `rlq_units_parent_period_guard`,
     `rlq_members_unit_period_guard`.

---

## L1 — Audit inventaris (read-only)

```sh
php artisan management:audit --json
```

- Menampilkan: daftar periode, aktif/tidak, jumlah unit, jumlah anggota,
  jumlah anggota tanpa wadah, dan DAFTAR JABATAN UNIK + cacahnya.
- **Tidak pernah menampilkan nama anggota** (anti-ekspos, aman dipindai).
- Catat output ini sebagai baseline pembanding setelah migrasi: jumlah
  anggota per periode dan daftar jabatan TIDAK BOLEH BERUBAH.

---

## L2 — Backup & restore-point (prasyarat migrasi)

```sh
# Backup custom format (tanpa --clean), simpan ke disk lokal:
pg_dump ^
  --host=%DB_HOST% --port=%DB_PORT% --username=%DB_USERNAME% ^
  --dbname=%DB_DATABASE% --format=c --no-owner --no-privileges ^
  --file=backup_pre_units_%DATE:/=-%.dump

# Verifikasi konten backup (restore-point terkonfirmasi):
pg_restore --list backup_pre_units_YYYY-MM-DD.dump > backup_pre_units.list
# Pastikan list memuat tabel: users, management_periods,
# management_members (dan sisanya), lalu arsipkan file .dump.
```

Alternatif restore point: snapshot/restore point Neon pada waktu tertentu.

Aturan: **L3 tidak boleh dijalankan tanpa `backup_pre_units_*.dump` yang
sudah diverifikasi `pg_restore --list`**.

---

## L3 — Migrasi skema produksi (terkondisi, satu perintah)

Migrasi yang dijalankan:
- `2026_11_07_000001_create_management_units_table.php` — tabel pohon unit
  (`UNIQUE(id, management_period_id)`, composite FK atau trigger sesuai
  versi PG).
- `2026_11_07_000002_add_unit_to_management_members.php` — kolom nullable
  `management_unit_id` + FK + **backfill idempoten**
  (`App\Support\ManagementBackfill`) yang HANYA menulisi kolom baru itu
  tanpa menyentuh jabatan/nama/urutan/periode.

```sh
# 1) Migrasi (idempoten; ulang aman):
php artisan migrate --force

# 2) Seeding admin (terkondisi; skip aman bila ADMIN_* tidak diisi):
php artisan db:seed --class=AdminUserSeeder --force
```

Catatan seeding: `AdminUserSeeder` punya skip-guard — bila variabel
`ADMIN_*` kosong, seeding tidak menulis apa pun (tidak ada akun baru
tercipta).

Kualifikasi keberhasilan L3:
1. `php artisan migrate:status` — semua `Ran`.
2. Data `management_members`: jabatan/nama/urutan/periode byte-identik
   dengan baseline L1 (count per periode + daftar jabatan tidak berubah).
3. `php artisan management:audit --json` — `members` per periode sama
   dengan baseline; `members_without_unit` jadi 0 (backfill berhasil).

---

## L4 — Deploy kode (Vercel)

Build (`composer vercel`) HANYA menjalankan:
1. `php artisan package:discover --ansi`
2. `php artisan filament:assets --ansi`
3. `php artisan schema:verify --ansi` — **read-only & fail-closed**:
   menolak bila (a) ada migrasi yang belum tercatat, (b) manifest
   tabel/kolom/index/constraint tidak lengkap. Jadi build GAGAL (deployment
   lama tetap melayani) bila skema lolos L3 belum.

Runtime HTTP TIDAK PERNAH menjalankan migrate/seed (guard tersebut sudah
dihapus dari `AppServiceProvider`).

Commit harus dibersihkan dari artefak lokal: `.env*`, file backup, dll.

---

## L5 — Verifikasi pasca-deploy (read-only)

```sh
php artisan schema:verify --json      # semua check hijau
php artisan management:audit --json   # seluruh periode utuh
```

- Buka halaman `/profil` (hargai cache: CSS `site.css?v=7`): halaman
  merender pohon dari
  `management_units`; anggota tampil pada wadah yang benar; periode aktif
 ilik satu-satunya yang tampil.
- Panel admin: periode → tab **Unit** (Tambah/Edit/Hapus, Unit induk
  anti-keturunan), tab **Anggota** (kolom Wadah), aksi **Salin struktur…**
  pada daftar periode.

---

## L6 — Rollback

### Rollback kode (tanpa skema)
```sh
git revert <commit-sha>        # commit perubahan fitur
# deploy ulang ke Vercel — kode lama + skema baru aman (additive)
```

### Rollback skema (perhatian!)
```sh
php artisan migrate:rollback \
  --step 2 --force             # menarik kedua migrasi baru
```
- `down()` migration units: drop trigger/FK lalu `dropIfExists('management_units')`.
- `down()` migration members: drop trigger/FK, drop index, drop kolom
  `management_unit_id` (data unit hilang; jabatan/nama/urutan/periode
  TIDAK tersentuh).
- **Keutamaan:** rollback via SQL raw juga valid untuk menghapus hanya
  bagian bahaya bila rollback Laravel macet.

### Pemulihan data dari backup `-Fc` (opsi terakhir, STOP-WORLD)
```sh
# Opsi a: restore penuh (dengan membersihkan objek lama):
pg_restore --host=%DB_HOST% --port=%DB_PORT% --username=%DB_USERNAME% ^
  --dbname=%DB_DATABASE% --clean --if-exists --no-owner --no-privileges ^
  backup_pre_units_YYYY-MM-DD.dump
# Opsi b: restore point / snapshot Neon pada waktu pra-L3.
```

---

## Suite B (pengujian database DB-level, tanpa menyentuh produksi)

Prasyarat: branch test Neon dengan database berawalan `rlq_test_`
(yang akan di-take-out migrasi ulang oleh RefreshDatabase), kredensial
khusus, bukan produksi.

```powershell
# Sesi PowerShell lokal:
$env:DB_HOST = "..."                  # host DIRECT branch TEST
$env:DB_PORT = "5432"
$env:DB_DATABASE = "rlq_test_*"       # WAJIB prefix ini (interlock)
$env:DB_USERNAME = "..."
$env:DB_PASSWORD = "..."
& 'C:\laragon\bin\php\php-8.3.32-Win32-vs16-x64\php.exe' `
  vendor\bin\phpunit --configuration phpunit.pg.xml
```

Isi Suite B (`tests/Db/ManagementCompositeFkDbTest.php`):
1. Unit dengan parent lintas periode ditolak DB (SQLSTATE 23503/P0001).
2. Anggota dengan wadah lintas periode ditolak DB.
3. Hapus unit → anggota selamat, `management_unit_id` SET NULL, periode utuh.
4. Hapus unit induk → anak dipromosikan ke root, periode tetap.
5. Hapus periode → cascade unit & anggota periode itu saja.
6. Definisi composite FK dicek; skip eksplisit bila PostgreSQL < 15
   (dilaporkan sebagai SKIPPED, bukan lulus).

---

## Cek cepat integritas (diagnostic read-only)

```sql
-- 1) Semua anggota menempel periode yang sama dengan wadahnya:
SELECT COUNT(*) AS salah
FROM management_members m
LEFT JOIN management_units u ON u.id = m.management_unit_id
WHERE m.management_unit_id IS NOT NULL
  AND coalesce(u.management_period_id, -1) <> m.management_period_id;
-- Harus 0 selamanya (dijamin FK/trigger; query ini hanya audit).

-- 2) Jumlah anggota per periode — harus identik dengan baseline L1:
SELECT management_period_id, COUNT(*) FROM management_members GROUP BY 1;
```
