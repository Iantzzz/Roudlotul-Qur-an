<?php

namespace Tests\Unit;

use App\Support\SocialVideo;
use Tests\TestCase;

class SocialVideoTest extends TestCase
{
    public function test_youtube_links_resolve_to_embed(): void
    {
        $links = [
            'https://youtu.be/ynJVEJJ38kw?si=wyf-RIx1WQ4anGN2',
            'https://www.youtube.com/watch?v=ynJVEJJ38kw&list=PLabc123',
            'https://youtube.com/shorts/ynJVEJJ38kw?feature=share',
            'https://www.youtube.com/embed/ynJVEJJ38kw',
            'https://www.youtube.com/live/ynJVEJJ38kw',
            'youtu.be/ynJVEJJ38kw',
            'ynJVEJJ38kw',
        ];

        foreach ($links as $link) {
            $video = SocialVideo::parse($link);

            $this->assertNotNull($video, "Tautan gagal diparse: {$link}");
            $this->assertSame('youtube', $video['platform'], "Platform salah: {$link}");
            $this->assertSame('embed', $video['mode'], "Mode salah: {$link}");
            $this->assertSame('https://www.youtube-nocookie.com/embed/ynJVEJJ38kw', $video['embed_url'], "Embed salah: {$link}");
            $this->assertNull($video['poster_url']);
            $this->assertSame('landscape', $video['aspect']);
        }
    }

    public function test_instagram_links_resolve_to_facade_with_poster(): void
    {
        $cases = [
            ['https://www.instagram.com/p/Df-mRDgT0nR/', 'https://www.instagram.com/p/Df-mRDgT0nR/embed/', 'portrait', 'https://www.instagram.com/p/Df-mRDgT0nR/media/?size=m'],
            ['https://www.instagram.com/reel/C1a2b3c4d5e/', 'https://www.instagram.com/reel/C1a2b3c4d5e/embed/', 'vertical', 'https://www.instagram.com/p/C1a2b3c4d5e/media/?size=m'],
            ['https://www.instagram.com/reels/C1a2b3c4d5e/', 'https://www.instagram.com/reel/C1a2b3c4d5e/embed/', 'vertical', 'https://www.instagram.com/p/C1a2b3c4d5e/media/?size=m'],
            ['https://www.instagram.com/tv/C1a2b3c4d5e/', 'https://www.instagram.com/tv/C1a2b3c4d5e/embed/', 'landscape', 'https://www.instagram.com/p/C1a2b3c4d5e/media/?size=m'],
        ];

        foreach ($cases as [$link, $embed, $aspect, $poster]) {
            $video = SocialVideo::parse($link);

            $this->assertNotNull($video, "Tautan gagal diparse: {$link}");
            $this->assertSame('instagram', $video['platform']);
            $this->assertSame('facade', $video['mode']);
            $this->assertSame($embed, $video['embed_url']);
            $this->assertSame($aspect, $video['aspect']);
            $this->assertSame(route('instagram.poster', ['code' => basename(parse_url($link, PHP_URL_PATH))]), $video['poster_url']);
        }
    }

    public function test_tiktok_links_resolve_to_embed(): void
    {
        $links = [
            'https://www.tiktok.com/@user/video/7234567890123456789',
            'https://www.tiktok.com/@user/photo/7234567890123456789',
            'https://www.tiktok.com/embed/v2/7234567890123456789',
        ];

        foreach ($links as $link) {
            $video = SocialVideo::parse($link);

            $this->assertNotNull($video, "Tautan gagal diparse: {$link}");
            $this->assertSame('tiktok', $video['platform']);
            $this->assertSame('embed', $video['mode']);
            $this->assertSame('https://www.tiktok.com/embed/v2/7234567890123456789', $video['embed_url']);
            $this->assertSame('vertical', $video['aspect']);
        }
    }

    public function test_facebook_video_resolves_to_plugin_embed(): void
    {
        $video = SocialVideo::parse('https://www.facebook.com/facebook/videos/10153231379946729/');

        $this->assertNotNull($video);
        $this->assertSame('facebook', $video['platform']);
        $this->assertNotNull($video['embed_url']);
        $this->assertStringStartsWith('https://www.facebook.com/plugins/video.php?href=', $video['embed_url']);
        $this->assertStringContainsString(
            rawurlencode('https://www.facebook.com/facebook/videos/10153231379946729/'),
            $video['embed_url'],
        );
        $this->assertSame('landscape', $video['aspect']);
    }

    public function test_vimeo_links_resolve_to_player_embed(): void
    {
        $video = SocialVideo::parse('https://vimeo.com/76979871');

        $this->assertNotNull($video);
        $this->assertSame('vimeo', $video['platform']);
        $this->assertSame('https://player.vimeo.com/video/76979871', $video['embed_url']);

        $unlisted = SocialVideo::parse('https://vimeo.com/76979871/abc123def');

        $this->assertNotNull($unlisted);
        $this->assertSame('https://player.vimeo.com/video/76979871?h=abc123def', $unlisted['embed_url']);
    }

    public function test_other_social_links_fall_back_to_external_url(): void
    {
        $cases = [
            'https://x.com/roudlotul/status/123',
            'https://www.linkedin.com/posts/roudlotul-activity-123',
            'https://example.com/video-asing',
        ];

        foreach ($cases as $link) {
            $video = SocialVideo::parse($link);

            $this->assertNotNull($video, "Tautan valid seharusnya tidak null: {$link}");
            $this->assertSame('link', $video['mode']);
            $this->assertNull($video['embed_url']);
            $this->assertNull($video['poster_url']);
            $this->assertSame($link, $video['url']);
        }
    }

    public function test_empty_or_invalid_links_return_null(): void
    {
        $this->assertNull(SocialVideo::parse(null));
        $this->assertNull(SocialVideo::parse(''));
        $this->assertNull(SocialVideo::parse('   '));
        $this->assertNull(SocialVideo::parse('bukan url'));
        $this->assertNull(SocialVideo::parse('ftp://example.com/video'));
    }
}
