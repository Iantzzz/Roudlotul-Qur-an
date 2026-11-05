<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
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
}
