<?php

namespace Tests\Feature;

use GuzzleHttp\Client;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class VercelAssetsTest extends TestCase
{
    private static string $directory;

    private static Process $server;

    private static Client $client;

    public static function setUpBeforeClass(): void
    {
        $root = dirname(__DIR__, 2);
        self::$directory = sys_get_temp_dir().'/rq-vercel-assets-'.bin2hex(random_bytes(8));
        $filesystem = new Filesystem;

        foreach (['api', 'vendor', 'public/css/filament/panel', 'public/js/filament/panel', 'public/fonts/filament/inter'] as $directory) {
            $filesystem->makeDirectory(self::$directory.'/'.$directory, 0755, true);
        }

        copy($root.'/api/index.php', self::$directory.'/api/index.php');
        file_put_contents(self::$directory.'/vendor/autoload.php', '<?php require '.var_export($root.'/vendor/autoload.php', true).';');
        file_put_contents(self::$directory.'/public/index.php', '<?php http_response_code(418); echo "Laravel entrypoint";');
        file_put_contents(self::$directory.'/public/css/filament/panel/app.css', 'body { color: red; }');
        file_put_contents(self::$directory.'/public/js/filament/panel/app.js', 'window.filamentAsset = true;');
        file_put_contents(self::$directory.'/public/fonts/filament/inter/font.woff2', 'font fixture');
        file_put_contents(self::$directory.'/public/css/private.css', 'private fixture');
        file_put_contents(self::$directory.'/public/css/filament/panel/private.php', '<?php echo "private source";');

        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $address = stream_socket_get_name($socket, false);
        fclose($socket);

        self::$server = new Process([PHP_BINARY, '-S', $address, '-t', self::$directory, self::$directory.'/api/index.php']);
        self::$server->start();
        self::assertTrue(self::$server->waitUntil(fn ($type, $output) => str_contains($output, 'Development Server')));
        self::$client = new Client(['base_uri' => 'http://'.$address, 'http_errors' => false, 'timeout' => 10]);
    }

    public static function tearDownAfterClass(): void
    {
        if (isset(self::$server)) {
            self::$server->stop();
        }

        if (isset(self::$directory)) {
            (new Filesystem)->deleteDirectory(self::$directory);
        }
    }

    #[DataProvider('assets')]
    public function test_assets_are_served_with_the_correct_type(string $path, string $type, string $content): void
    {
        $response = self::$client->get($path.'?v=test');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringStartsWith($type, $response->getHeaderLine('Content-Type'));
        $this->assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
        $this->assertStringContainsString('public', $response->getHeaderLine('Cache-Control'));
        $this->assertFalse($response->hasHeader('Set-Cookie'));
        $this->assertSame($content, (string) $response->getBody());
    }

    public static function assets(): array
    {
        return [
            ['/css/filament/panel/app.css', 'text/css', 'body { color: red; }'],
            ['/js/filament/panel/app.js', 'application/javascript', 'window.filamentAsset = true;'],
            ['/fonts/filament/inter/font.woff2', 'font/woff2', 'font fixture'],
        ];
    }

    #[DataProvider('unavailableAssets')]
    public function test_missing_and_private_files_are_not_served(string $path): void
    {
        $response = self::$client->get($path);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('', (string) $response->getBody());
    }

    public static function unavailableAssets(): array
    {
        return [
            ['/css/filament/missing.css'],
            ['/css/filament/panel'],
            ['/css/filament/panel/private.php'],
            ['/css/filament/%2e%2e/private.css'],
            ['/css/filament/..%5cprivate.css'],
            ['/css/filament/panel/app.css%00'],
            ['/css/filament/%252e%252e/private.css'],
        ];
    }

    public function test_head_and_conditional_requests_are_supported(): void
    {
        $path = '/css/filament/panel/app.css';
        $response = self::$client->head($path);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', (string) $response->getBody());
        $this->assertSame('20', $response->getHeaderLine('Content-Length'));

        $cached = self::$client->get($path, ['headers' => ['If-None-Match' => $response->getHeaderLine('ETag')]]);
        $this->assertSame(304, $cached->getStatusCode());
        $this->assertSame('', (string) $cached->getBody());
    }

    public function test_write_requests_are_rejected(): void
    {
        $response = self::$client->post('/css/filament/panel/app.css');

        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame('GET, HEAD', $response->getHeaderLine('Allow'));
    }

    public function test_other_requests_continue_to_laravel(): void
    {
        $response = self::$client->get('/admin/login');

        $this->assertSame(418, $response->getStatusCode());
        $this->assertSame('Laravel entrypoint', (string) $response->getBody());
    }
}
