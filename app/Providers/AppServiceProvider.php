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
     * yang benar-benar dipakai runtime tidak menerima hasilnya
     * (SQLSTATE 42P01 ketika login admin dicoba).
     *
     * Guard ini mengurus dua hal yang saling lepas, di database yang
     * dipakai aplikasi saat ini:
     *   1. Bila tabel `users` tidak ada → jalankan migrasi. Idempotent
     *      dan aman dijalankan berulang (tercatat di tabel `migrations`);
     *      bila satu instans terpotong batas waktu serverless, instans
     *      berikutnya melanjutkan sisa migrasi.
     *   2. Bila ADMIN_EMAIL & ADMIN_PASSWORD terisi tetapi row user
     *      admin belum ada → jalankan seeder admin (idempotent,
     *      updateOrCreate).
     *
     * Hapus/kecilkan metode ini setelah integrasi Neon-Vercel
     * dikonfigurasikan agar build & runtime memakai branch database
     * yang sama.
     */
    private function ensureDatabaseReady(): void
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

        try {
            if ($usersTableMissing) {
                Artisan::call('migrate', ['--force' => true]);
                Log::warning('auto-restore: migrasi dijalankan di runtime (tabel users tidak ada)');
            }

            $this->seedAdminIfNeeded();
        } catch (Throwable $e) {
            Log::error('auto-restore: gagal memperbaiki database runtime', ['error' => mb_substr((string) $e, 0, 400)]);
        }
    }

    /**
     * Buat user admin Filament bila env ADMIN_* terisi tetapi row user
     * dengan email tersebut belum ada. Aman untuk dijalankan berulang.
     */
    private function seedAdminIfNeeded(): void
    {
        $email = trim((string) env('ADMIN_EMAIL', ''));
        $password = (string) env('ADMIN_PASSWORD', '');

        if ($email === '' || $password === '') {
            return; // env memang belum diisi — seeder juga akan melewatinya
        }

        $exists = (bool) DB::table('users')->where('email', $email)->exists();

        if ($exists) {
            return;
        }

        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\AdminUserSeeder', '--force' => true]);
        Log::warning('auto-restore: user admin dibuat di runtime');
    }
}
