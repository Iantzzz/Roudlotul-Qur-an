<?php

namespace Tests\Feature;

use App\Filament\Resources\Greetings\Pages\EditGreeting;
use App\Models\Greeting;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminContentSaveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'admin.email' => 'admin-uji@roudlot.test',
            'admin.password' => 'sandi-uji-123',
            'admin.name' => 'Admin Uji',
        ]);

        (new AdminUserSeeder)->run();

        $this->actingAs(User::query()->where('email', 'admin-uji@roudlot.test')->firstOrFail());

        Filament::setCurrentPanel('admin');
    }

    /**
     * Regresi: trait ManagesSiteMediaUpload pernah memanggil parent::afterSave()
     * yang tidak ada di EditRecord, sehingga setiap simpan mengembalikan HTTP 500
     * (data sudah ter-commit karena panel tidak memakai database transaction).
     */
    public function test_editing_a_greeting_saves_without_throwing(): void
    {
        $greeting = Greeting::create([
            'name' => 'Nama Lama',
            'role' => 'Jabatan Lama',
            'body' => 'Isi lama',
            'is_active' => true,
        ]);

        Livewire::test(EditGreeting::class, ['record' => $greeting->getRouteKey()])
            ->fillForm([
                'name' => 'Nama Baru',
                'role' => 'Jabatan Baru',
                'body' => 'Isi baru',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('greetings', [
            'id' => $greeting->id,
            'name' => 'Nama Baru',
            'role' => 'Jabatan Baru',
            'body' => 'Isi baru',
        ]);
    }
}
