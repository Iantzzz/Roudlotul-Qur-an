<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensureDatabaseSchemaReady();
    }

    /**
     * SEMENTARA (perbaikan produksi Vercel):
     * Integrasi Neon-Vercel menjalankan migrasi build-time pada branch
     * database sementara yang dihapus setelah build, sehingga database
     * yang benar-benar dipakai runtime tidak menerima hasilnya
     * (SQLSTATE 42P01 ketika login admin dicoba).
     *
     * Guard ini HANYA memulihkan skema, idempotent: bila salah satu tabel
     * inti (users, management_periods, management_members) tidak ada,
     * jalankan migrasi sekali per instans; bila satu instans terpotong
     * (batas waktu serverless), instans berikutnya melanjutkan sisa
     * migrasi. Bila skema sudah lengkap, guard hanya melakukan satu probe
     * baca per instans lalu selesai — tidak ada penulisan database per
     * request.
     *
     * Pembuatan akun admin BUKAN tugas metode ini: berjalan lewat
     * Database\Seeders\AdminUserSeeder dari proses terkontrol
     * (endpoint diagnostik / composer build), bukan per HTTP request.
     *
     * Hapus metode ini setelah integrasi Neon-Vercel dikonfigurasikan
     * agar build & runtime memakai branch database yang sama.
     */
    private function ensureDatabaseSchemaReady(): void
    {
        static $checked = false;

        if ($checked || $this->app->runningUnitTests() || $this->app->runningInConsole()) {
            return;
        }
        $checked = true;

        try {
            $missingTables = (int) (DB::select(
                "SELECT COUNT(*) AS missing
                FROM (VALUES ('users'), ('management_periods'), ('management_members')) AS t(name)
                WHERE NOT EXISTS (
                    SELECT 1 FROM information_schema.tables
                    WHERE table_schema = 'public' AND table_name = t.name
                )"
            )[0]->missing ?? 1);
        } catch (Throwable $e) {
            // Koneksi gagal bukan urusan guard ini; biarkan aplikasi
            // menangani error database seperti biasa.
            return;
        }

        if ($missingTables === 0) {
            return;
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            Log::warning('auto-restore: migrasi dijalankan di runtime (tabel inti tidak lengkap)');

            return;
        } catch (Throwable $e) {
            Log::error('auto-restore: migrasi via koneksi utama gagal', ['error' => mb_substr((string) $e, 0, 300)]);
        }

        // Neon menghadiri PgBouncer dalam mode transaction: satu statement DDL
        // yang gagal membuat sisa transaksi terbuang ("In failed sql transaction").
        // Ujicoba berikutnya berjangkat ke endpoint DIRECT tanpa pooler.
        $rawUrl = (string) (getenv('DATABASE_URL') ?: '');
        $parts = parse_url($rawUrl) ?: [];
        $host = is_array($parts) ? ($parts['host'] ?? null) : null;

        if (is_string($host) && str_contains($host, '-pooler')) {
            $previousUrl = config('database.connections.pgsql.url');

            config(['database.connections.pgsql.url' => str_replace('-pooler.', '.', $rawUrl)]);
            DB::purge('pgsql');

            try {
                Artisan::call('migrate', ['--force' => true]);
                Log::warning('auto-restore: migrasi berhasil via koneksi DIRECT (tanpa pooler)');
            } catch (Throwable $e) {
                Log::error('auto-restore: migrasi via DIRECT juga gagal', ['error' => mb_substr((string) $e, 0, 300)]);
            } finally {
                if ($previousUrl !== null) {
                    config(['database.connections.pgsql.url' => $previousUrl]);
                }

                DB::purge('pgsql');
            }
        }
    }
}
