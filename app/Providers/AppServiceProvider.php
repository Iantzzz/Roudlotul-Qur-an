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
     * Guard ini HANYA memulihkan skema, idempotent: bila tabel `users`
     * tidak ada, jalankan migrasi sekali per instans; bila satu instans
     * terpotong (batas waktu serverless), instans berikutnya
     * melanjutkan sisa migrasi. Bila skema sudah lengkap, guard hanya
     * melakukan satu probe baca per instans lalu selesai — tidak ada
     * penulisan database per request.
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
            $usersTableMissing = (bool) (DB::select(
                "SELECT NOT EXISTS (
                    SELECT 1 FROM information_schema.tables
                    WHERE table_schema = 'public' AND table_name = 'users'
                ) AS missing"
            )[0]->missing ?? true);
        } catch (Throwable $e) {
            // Koneksi gagal bukan urusan guard ini; biarkan aplikasi
            // menangani error database seperti biasa.
            return;
        }

        if (! $usersTableMissing) {
            return;
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            Log::warning('auto-restore: migrasi dijalankan di runtime (tabel users tidak ada)');
        } catch (Throwable $e) {
            Log::error('auto-restore: gagal memperbaiki skema database runtime', ['error' => mb_substr((string) $e, 0, 400)]);
        }
    }
}
