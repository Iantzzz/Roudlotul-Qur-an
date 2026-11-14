<?php

namespace Tests\Feature;

use App\Models\SiteMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_route_serves_stored_binary_with_cache_headers(): void
    {
        $media = SiteMedia::create([
            'name' => 'uji.png',
            'mime' => 'image/png',
            'data' => base64_encode('biner-uji'),
            'size' => 9,
        ]);

        $this->get('/media/'.$media->getKey())
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            // Symfony menormalkan urutan direktif Cache-Control.
            ->assertHeaderContains('Cache-Control', 'max-age=31536000')
            ->assertHeaderContains('Cache-Control', 'immutable')
            ->assertHeaderContains('Cache-Control', 'public')
            ->assertSee('biner-uji');
    }

    public function test_unknown_media_id_returns_not_found(): void
    {
        $this->get('/media/99999')->assertNotFound();
    }

    public function test_media_data_is_hidden_from_serialization(): void
    {
        $media = SiteMedia::create([
            'name' => 'uji.png',
            'mime' => 'image/png',
            'data' => base64_encode('biner-uji'),
            'size' => 9,
        ]);

        $this->assertArrayNotHasKey('data', $media->toArray());
    }
}
