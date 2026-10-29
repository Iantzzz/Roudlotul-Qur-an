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
        $this->ensureDatabaseReady();
    }

    /**
     * SEMENTARA (perbaikan produksi Vercel):
     * Integrasi Neon-Vercel menjalankan migrasi build-time pada branch
     * database sementara yang dihapus setelah build, sehingga database
     * yang benar-benar dipakai runtime bisa lengkap tanpa tabel
     * (SQLSTATE 42P01 ketika login admin dicoba).
     *
     * Guard ini memastikan skema tersedia di database yang dipakai
     * aplikasi saat ini: bila tabel `users` tidak ada (string penyebab
     * SQLSTATE 42P01 saat login), jalankan migrasi + seeder admin sekali
     * per instans. Idempotent dan aman dijalankan berulang karena
     * migrasi Laravel tercatat di tabel `migrations`; bila satu instans
     * terpotong (batas waktu serverless), instans berikutnya
     * melanjutkan sisa migrasi.
     *
     * Hapus metode ini setelah integrasi Neon-Vercel dikonfigurasikan
     * agar build & runtime memakai branch database yang sama, atau
     * migrasi pindah ke kanal yang andal.
     */
    private function ensureDatabaseReady(): void
    {
        static $checked = false;

        if ($checked || $this->app->runningUnitTests() || $this->app->runningInConsole()) {
            return;
        }
        $checked = true;

        try {
            $missing = DB::select(
                "SELECT NOT EXISTS (
                    SELECT 1 FROM information_schema.tables
                    WHERE table_schema = 'public' AND table_name = 'users'
                ) AS missing"
            )[0]->missing ?? true;

            if (! $missing) {
                return;
            }
        } catch (Throwable $e) {
            // Koneksi gagal bukan urusan guard ini; biarkan aplikasi
            // menangani error database seperti biasa.
            return;
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call(
                'db:seed',
                ['--class' => 'Database\\Seeders\\AdminUserSeeder', '--force' => true],
            );
            Log::warning('auto-restore: migrasi + seeder dijalankan di runtime (database runtime tanpa tabel)');
        } catch (Throwable $e) {
            Log::error('auto-restore: gagal memperbaiki skema database runtime', ['error' => mb_substr((string) $e, 0, 400)]);
        }
    }
}
