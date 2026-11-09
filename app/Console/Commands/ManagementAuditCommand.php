<?php

namespace App\Console\Commands;

use App\Models\ManagementPeriod;
use App\Support\ManagementStructure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Audit READ-ONLY seluruh periode kepengurusan.
 *
 * - Dijalankan HANYA dari Food Artisan (console) — tidak ada
 *   HTTP route yang mengarah ke sini, sehingga tidak dapat
 *   diakses dari luar.
 * - Output TIDAK PERNAH menampilkan nama anggota maupun
 *   kredensial database: hanya label periode, jumlah unit,
 *   jumlah anggota, dan daftar nilai jabatan unik + cacahnya.
 * - Dipakai sebagai langkah 0 (audit inventaris) sebelum
 *   menentukan mapping backfill.
 */
class ManagementAuditCommand extends Command
{
    protected $signature = 'management:audit {--json : Output JSON lengkap}';

    protected $description = 'Audit read-only semua periode kepengurusan (unit, jumlah anggota, jabatan unik — tanpa nama anggota).';

    public function handle(): int
    {
        $periods = ManagementPeriod::query()
            ->with('members')
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        $unitsReady = Schema::hasTable('management_units');
        $unitColumnReady = Schema::hasColumn('management_members', 'management_unit_id');
        $postgresVersion = ManagementStructure::postgresVersion();

        $data = [
            'generated_at' => now()->toIso8601String(),
            'schema' => [
                'management_units' => $unitsReady,
                'management_members.management_unit_id' => $unitColumnReady,
                'postgres_version' => $postgresVersion,
            ],
            'periods' => [],
        ];

        foreach ($periods as $period) {
            $memberCount = $period->members()->count();
            $unitCount = $unitsReady ? $period->units()->count() : null;
            $orphanCount = $unitColumnReady
                ? $period->members()->whereNull('management_unit_id')->count()
                : null;

            $jabatan = $period->members
                ->groupBy(fn ($member) => (string) $member->jabatan)
                ->map->count()
                ->sortKeys()
                ->all();

            $data['periods'][] = [
                'id' => (int) $period->getKey(),
                'label' => (string) $period->label,
                'is_active' => (bool) $period->is_active,
                'units' => $unitCount,
                'members' => $memberCount,
                'members_without_unit' => $orphanCount,
                'jabatan' => $jabatan,
            ];
        }

        if ((bool) $this->option('json')) {
            $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        $this->info('=== AUDIT STRUKTUR KEPENGURUSAN (read-only) ===');
        $this->line('Tabel management_units  : '.($unitsReady ? 'ADA' : 'BELUM ADA (skema belum diupgrade)'));
        $this->line('Kolom management_unit_id: '.($unitColumnReady ? 'ADA' : 'BELUM ADA'));
        $this->line('PostgreSQL              : '.($postgresVersion !== null ? $postgresVersion : '(bukan pgsql)'));

        if ($periods->isEmpty()) {
            $this->warn('Tidak ada periode kepengurusan di database.');

            return self::SUCCESS;
        }

        $this->table(
            ['Periode', 'Aktif', 'Unit', 'Anggota', 'Tanpa wadah', 'Jabatan unik'],
            collect($data['periods'])->map(fn (array $row): array => [
                $row['label'],
                $row['is_active'] ? 'YA' : '-',
                $row['units'] ?? '?',
                (string) $row['members'],
                $row['members_without_unit'] ?? '?',
                implode(', ', array_map(
                    fn (string $name, int $count): string => $name.' ×'.$count,
                    array_keys($row['jabatan']),
                    array_values($row['jabatan']),
                )),
            ])->all(),
        );

        $this->line('Catatan: daftar jabatan TIDAK menampilkan nama anggota (anti-ekspos).');

        return self::SUCCESS;
    }
}
