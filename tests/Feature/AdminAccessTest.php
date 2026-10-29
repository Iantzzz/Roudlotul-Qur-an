<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_access_is_granted_only_for_the_provisioned_admin_email(): void
    {
        config(['admin.email' => 'admin-uji@roudlot.test']);

        $panel = Filament::getCurrentOrDefaultPanel();

        $this->assertSame('admin', $panel->getId());
        $this->assertTrue((new User(['email' => 'admin-uji@roudlot.test']))->canAccessPanel($panel));
        $this->assertFalse((new User(['email' => 'petugas-lain@roudlot.test']))->canAccessPanel($panel));
        $this->assertFalse((new User(['email' => '']))->canAccessPanel($panel));
    }

    public function test_panel_access_is_denied_when_admin_email_is_not_configured(): void
    {
        config(['admin.email' => '']);

        $panel = Filament::getCurrentOrDefaultPanel();

        $this->assertFalse((new User(['email' => 'siapapun@roudlot.test']))->canAccessPanel($panel));
    }

    public function test_seeder_skips_when_admin_credentials_are_missing(): void
    {
        config(['admin.email' => '', 'admin.password' => '']);

        (new AdminUserSeeder)->run();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_seeder_creates_verified_admin_once_and_never_overwrites_existing_account(): void
    {
        config([
            'admin.email' => 'admin-uji@roudlot.test',
            'admin.password' => 'sandi-uji-123',
            'admin.name' => 'Admin Uji',
        ]);

        (new AdminUserSeeder)->run();

        $user = User::query()->where('email', 'admin-uji@roudlot.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('Admin Uji', $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('sandi-uji-123', $user->password));
        $this->assertTrue($user->canAccessPanel(Filament::getCurrentOrDefaultPanel()));

        // Simulasi akun lama: seeder dijalankan ulang TIDAK boleh mengubah apa pun.
        $user->forceFill([
            'name' => 'Nama Lama',
            'password' => Hash::make('sandi-lama'),
            'email_verified_at' => null,
        ])->save();

        (new AdminUserSeeder)->run();

        $user->refresh();
        $this->assertSame('Nama Lama', $user->name);
        $this->assertTrue(Hash::check('sandi-lama', $user->password));
        $this->assertNull($user->email_verified_at);
        $this->assertDatabaseCount('users', 1);
    }
}
