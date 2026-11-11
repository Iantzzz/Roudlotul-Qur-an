<?php

namespace Tests\Db;

use App\Models\ManagementPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Throwable;

/**
 * Basis Suite B (database-level, PostgreSQL):
 *
 * INTERLOCK — wajib terpenuhi SEBELUM RefreshDatabase dijalankan:
 * 1. driver HARUS pgsql (PHPUnit tidak pernah memakai kredensial
 *    produksi; phpunit.pg.xml mengarah ke variabel khusus testing);
 * 2. current_database() HARUS berawalan "rlq_test_" — bila tidak,
 *    run dihentikan sebelum satupun migrasi dijalankan, sehingga
 *    database produksi mustahil tersentuh migrate:fresh.
 *
 * Tujuannya: satu-satunya database yang boleh dipakai PHPUnit adalah
 * branch Neon test (prefix rlq_test_). Cek host/kredensial berada di
 * phpunit.pg.xml (nilai dari env sesi); interlock ini menutup sisanya
 * di dalam proses.
 */
abstract class DbPgsqlTestCase extends TestCase
{
    use RefreshDatabase;

    public const TEST_DB_PREFIX = 'rlq_test_';

    protected function setUp(): void
    {
        // Bootstrap aplikasi + facade terlebih dahulu (TANPA memicu
        // RefreshDatabase — itu dijalankan parent via setUpTraits setelah
        // baris ini), sehingga interlock punya koneksi DB aktif yang
        // bisa diverifikasi sebelum satu pun migrasi berjalan.
        if (null === $this->app) {
            $this->createApplication();
        }

        // Interlock dijalankan SEBELUM apa pun dari parent —
        // termasuk RefreshDatabase yang dipicu setUpTraits().
        self::assertTestDatabaseInterlock();

        parent::setUp();
    }

    /**
     * overwrite trait hook RefreshDatabase: interlock sudah dilakukan
     * di setUp; tetap diperiksa lagi di sini (defense in depth) sebelum
     * migrate:fresh dieksekusi.
     */
    protected function refreshDatabase(): void
    {
        self::assertTestDatabaseInterlock();

        $this->beforeRefreshingDatabase();

        if ($this->usingInMemoryDatabases()) {
            $this->restoreInMemoryDatabase();
        }

        $this->refreshTestDatabase();

        $this->afterRefreshingDatabase();
    }

    /**
     * Interlock: driver pgsql + nama database berawalan rlq_test_.
     * Pengecualian manapun di sini menghentikan suite SEBELUM migrasi.
     */
    private static function assertTestDatabaseInterlock(): void
    {
        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();

            if ($driver !== 'pgsql') {
                throw new \RuntimeException(sprintf(
                    'Suite B hanya boleh dijalankan dengan driver pgsql (driver saat ini: %s). Kredensial produksi tidak boleh dipakai untuk PHPUnit.',
                    $driver,
                ));
            }

            $database = (string) $connection->selectOne('SELECT current_database() AS db')->db;

            if (! str_starts_with($database, self::TEST_DB_PREFIX)) {
                throw new \RuntimeException(sprintf(
                    'Database "%s" TIDAK berawalan "rlq_test_". Suite B hanyalah branch DB testing (prefix "rlq_test_"); jalankan ulang dengan kredensial khusus testing.',
                    $database,
                ));
            }
        } catch (Throwable $e) {
            fwrite(STDERR, PHP_EOL.'[INTERLOCK SUITE B] '.$e->getMessage().PHP_EOL);

            throw $e;
        }
    }

    /**
     * DB tujuan + driver (untuk laporan/diagnostik run).
     */
    protected static function targetDatabase(): string
    {
        return (string) DB::connection()->selectOne('SELECT current_database() AS db')->db;
    }

    /**
     * Periode sintetis ringkas.
     */
    protected function makePeriod(string $label = 'Periode Uji 1', bool $isActive = false): ManagementPeriod
    {
        return ManagementPeriod::create([
            'label' => $label,
            'is_active' => $isActive,
            'urutan' => 1,
        ]);
    }

    /**
     * Singkirkan kesan tersisa: SQLSTATE dari exception query terakhir.
     */
    protected static function sqlState(Throwable $e): ?string
    {
        $previous = $e;

        while ($previous !== null) {
            if (property_exists($previous, 'errorInfo') && is_array($previous->errorInfo ?? null) && isset($previous->errorInfo[0])) {
                return (string) $previous->errorInfo[0];
            }

            if (preg_match("/SQLSTATE\[([0-9A-Za-z]{5})\]/", $previous->getMessage(), $matches)) {
                return $matches[1];
            }

            $previous = $previous->getPrevious();
        }

        return null;
    }
}
