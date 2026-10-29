<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Membuat user admin Filament dari variabel lingkungan (ADMIN_*).
 * Dipakai pada deployment Vercel (script composer "vercel") dan lewat
 * endpoint diagnostik terkontrol.
 *
 * Idempotent: membuat akun hanya bila belum ada (firstOrCreate) dan
 * TIDAK PERNAH menimpa password/nama akun yang sudah ada, sehingga
 * menjalankan ulang tidak mengubah apa pun.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) config('admin.email', ''));
        $password = (string) config('admin.password', '');

        if ($email === '' || $password === '') {
            $this->command?->line('ADMIN_EMAIL/ADMIN_PASSWORD belum diisi, seeder admin dilewati.');

            return;
        }

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => (string) config('admin.name', 'Admin'),
                'password' => Hash::make($password),
            ],
        );

        if ($user->wasRecentlyCreated && $user->email_verified_at === null) {
            // Panel admin memerlukan email terverifikasi; akun baru langsung
            // ditandai terverifikasi karena dibuat oleh pemiliknya sendiri.
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $this->command?->line(
            $user->wasRecentlyCreated
                ? "User admin dibuat untuk email tersebut."
                : 'Akun admin sudah ada, tidak ada yang diubah.'
        );
    }
}
