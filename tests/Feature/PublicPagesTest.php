<?php

namespace Tests\Feature;

use App\Models\ManagementPeriod;
use App\Models\ManagementUnit;
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
            ->assertDontSee('org-chart')
            ->assertDontSee('org-period');
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

        // Periode aktif punya anggota tetapi belum punya unit pohon:
        // seluruh anggota tampil pada satu wadah dinamis "Struktur {label}".
        $this->get('/profil')
            ->assertOk()
            ->assertSee('org-chart')
            ->assertSee('org-branch')
            ->assertSee('Periode 2025/2026')
            ->assertSee('Struktur 2025/2026')
            ->assertSee('Pembina')
            ->assertSee('Nama Anggota Satu')
            ->assertSee('Nama Anggota Dua')
            ->assertDontSee('Periode 2024/2025');
    }

    public function test_profile_renders_saved_unit_tree_with_depth_classes(): void
    {
        $active = ManagementPeriod::create([
            'label' => '2026/2027',
            'is_active' => true,
            'urutan' => 1,
        ]);

        $root = ManagementUnit::create([
            'management_period_id' => (int) $active->getKey(),
            'nama' => 'Pengasuh',
            'urutan' => 1,
        ]);

        ManagementUnit::create([
            'management_period_id' => (int) $active->getKey(),
            'parent_id' => (int) $root->getKey(),
            'nama' => 'Ketua Pembina',
            'urutan' => 1,
        ]);

        $this->get('/profil')
            ->assertOk()
            ->assertSee('org-chart')
            ->assertSee('org-branch--depth-1', false)
            ->assertSee('org-branch--depth-2', false)
            ->assertSee('Pengasuh')
            ->assertSee('Ketua Pembina');
    }
}
