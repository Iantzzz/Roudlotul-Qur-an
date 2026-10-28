<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Membuat user admin Filament dari variabel lingkungan (ADMIN_*).
 * Dipakai pada deployment Vercel (script composer "vercel").
 * Aman dijalankan berulang: lewati bila env belum diisi atau email telah ada.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', '');
        $password = (string) env('ADMIN_PASSWORD', '');

        if ($email === '' || $password === '') {
            $this->command?->line('ADMIN_EMAIL/ADMIN_PASSWORD belum diisi, seeder admin dilewati.');

            return;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) env('ADMIN_NAME', 'Admin'),
                'password' => Hash::make($password),
            ],
        );
    }
}
