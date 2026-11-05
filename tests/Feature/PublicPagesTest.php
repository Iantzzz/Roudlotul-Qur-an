<?php

namespace Tests\Feature;

use App\Models\ManagementPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_return_a_successful_response(): void
    {
        $paths = [
            '/',
            '/profil',
            '/kegiatan',
            '/galeri',
            '/berita',
            '/berita/hari-santri-nasional-2026',
            '/pendaftaran',
            '/kontak',
        ];

        foreach ($paths as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_news_can_be_filtered_by_category(): void
    {
        $this->get('/berita?kategori=kabar')
            ->assertOk()
            ->assertSee('Selamat Hari Santri Nasional 2026')
            ->assertSee("Ta'dhim Maulid Nabi 1448 H")
            ->assertDontSee('Kajian Tafsir Jalalain Berlangsung Rutin');
    }

    public function test_unknown_news_slug_returns_not_found(): void
    {
        $this->get('/berita/slug-yang-tidak-ada')->assertNotFound();
    }

    public function test_unknown_route_renders_the_custom_404_page(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan.');
    }

    public function test_gallery_sections_render_the_carousel(): void
    {
        foreach (['/', '/galeri'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('data-gallery-carousel')
                ->assertSee('data-gallery-track')
                ->assertSee('data-gallery-prev')
                ->assertSee('data-gallery-next')
                ->assertSee('data-gallery-dots');
        }
    }

    public function test_split_media_frames_render_the_photo_slider(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-split-slider')
            ->assertSee('quran-detail.webp')
            ->assertSee('courtyard.webp');

        $this->get('/profil')
            ->assertOk()
            ->assertSee('data-split-slider')
            ->assertSee('mosque-light.webp')
            ->assertSee('calligraphy-gold.webp');
    }

    public function test_profile_shows_management_placeholder_when_no_period_is_active(): void
    {
        $this->get('/profil')
            ->assertOk()
            ->assertSee('Struktur Kepengurusan')
            ->assertSee('Struktur belum dipublikasikan')
            ->assertDontSee('management-grid');
    }

    public function test_profile_renders_only_the_active_period_and_its_members(): void
    {
        $active = ManagementPeriod::create([
            'label' => '2025/2026',
            'is_active' => true,
            'urutan' => 1,
        ]);

        $active->members()->createMany([
            ['jabatan' => 'Pembina', 'nama' => 'Nama Anggota Satu', 'urutan' => 1],
            ['jabatan' => 'Ketua', 'nama' => 'Nama Anggota Dua', 'urutan' => 2],
        ]);

        ManagementPeriod::create([
            'label' => '2024/2025',
            'urutan' => 2,
        ]);

        $this->get('/profil')
            ->assertOk()
            ->assertSee('Kepengurusan')
            ->assertSee('2025/2026')
            ->assertSee('Pembina')
            ->assertSee('Nama Anggota Satu')
            ->assertSee('Nama Anggota Dua')
            ->assertDontSee('2024/2025');
    }
}
