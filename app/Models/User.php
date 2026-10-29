<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Izin masuk panel admin Filament hanya diberikan kepada akun admin
     * yang diprovisi dari environment (ADMIN_EMAIL) — pengguna lain
     * selalu ditolak dengan 403. Tanpa konfigurasi ini panel terkunci
     * penuh untuk semua orang.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        $adminEmail = trim((string) config('admin.email', ''));

        return $panel->getId() === 'admin'
            && $adminEmail !== ''
            && hash_equals($adminEmail, (string) $this->email);
    }
}
