<?php

namespace App\Support;

use App\Models\ManagementMember;
use App\Models\ManagementPeriod;
use App\Models\ManagementUnit;
use Illuminate\Support\Facades\DB;
use Traversable;

/**
 * Backfill satu kali: memetakan seluruh anggota lama ke wadah (unit)
 * TANPA mengubah jabatan, nama, urutan, ataupun periode — hanya kolom
 * baru management_unit_id yang ditulisi.
 *
 * Idempotensi dua lapis:
 * 1. dipanggil dari file migrasi (blok via tabel migrations), dan
 * 2. skip internal: periode yang SUDAH punya unit dilewati, sehingga
 *    pemanggilan ulang (double-run) mustahil menciptakan duplikat.
 *
 * Periode yang pola jabatannya tidak dikenali → satu wadah tunggal
 * "Struktur {label}" dan SEMUA anggotanya tampil di sana.
 */
final class ManagementBackfill
{
    /** Pemetaan jabatan persis (perbandingan lowercase, tidak mengubah data). */
    private const EXACT_MAP = [
        'pengasuh' => 'pengasuh',
        'pembina penasehat' => 'pembina-penasehat',
        'ketua pembina' => 'ketua-pembina',
        'sekretaris' => 'pengurus-inti',
        'bendahara' => 'pengurus-inti',
        'sarpras' => 'bidang:sarpras',
    ];

    /** Prefiks peran yang dilepas untuk menemukan nama bidangnya. */
    private const PREFIXES = ['koord.', 'koordinator', 'anggota', 'tim'];

    /** Nama bidang kanonik (kunci lowercase; nilai dipakai sebagai nama unit). */
    private const BIDANG_MAP = [
        'kedisiplinan' => 'Kedisiplinan',
        'media it' => 'Media IT',
        'sarpras' => 'Sarana & Prasarana',
        'sarana & prasarana' => 'Sarana & Prasarana',
        'kebersihan' => 'Kebersihan',
        'kesenian & olahraga' => 'Kesenian & Olahraga',
        'kesenian dan olahraga' => 'Kesenian & Olahraga',
        'kesenian olahraga' => 'Kesenian & Olahraga',
    ];

    /**
     * Jalankan backfill. Dalam transaksi; aman diulang.
     *
     * @return array<int, array{period: string, mode: string, units: int, members: int}>
     */
    public static function run(): array
    {
        return DB::transaction(function () {
            $summary = [];

            $periods = ManagementPeriod::query()
                ->with('members')
                ->orderBy('urutan')
                ->orderBy('id')
                ->get();

            foreach ($periods as $period) {
                // Guard internal: periode sudah punya struktur → dilewati.
                if ($period->units()->exists()) {
                    $summary[] = [
                        'period' => (string) $period->label,
                        'mode' => 'skipped-already-structured',
                        'units' => $period->units()->count(),
                        'members' => $period->members()->count(),
                    ];

                    continue;
                }

                $summary[] = self::backfillPeriod($period);
            }

            return $summary;
        });
    }

    /**
     * @return array{period: string, mode: string, units: int, members: int}
     */
    private static function backfillPeriod(ManagementPeriod $period): array
    {
        /** @var ManagementMember[] $members */
        $members = $period->members->all();

        if ($members === []) {
            return [
                'period' => (string) $period->label,
                'mode' => 'empty-period',
                'units' => 0,
                'members' => 0,
            ];
        }

        // Klasifikasi seluruh anggota dulu: satu jabatan tak dikenal
        // memicu wadah tunggal untuk SELURUH periode (tidak ada yang
        // pernah hilang dari tampilan).
        $buckets = [];
        $bidangOrder = [];

        foreach ($members as $member) {
            $classification = self::classify((string) $member->jabatan);

            if ($classification === null) {
                return self::backfillSingleContainer($period, $members);
            }

            [$key, $bucketName] = $classification;

            if (! isset($buckets[$key])) {
                $buckets[$key] = [];

                if ($bucketName !== null) {
                    $bidangOrder[] = [$key, $bucketName];
                }
            }

            $buckets[$key][] = $member;
        }

        $created = 0;

        // Tingkat 1 (urutan): Pengasuh → Pengurus Inti → Bidang Kepengurusan.
        $pengasuhUnit = null;
        $nextRootUrutan = 1;

        if (isset($buckets['pengasuh'])) {
            $pengasuhUnit = self::createUnit($period, null, 'Pengasuh', $nextRootUrutan++);
            $created++;

            self::attachAll($buckets['pengasuh'], $pengasuhUnit);
        }

        $coreUnit = null;

        if (isset($buckets['pengurus-inti'])) {
            $coreUnit = self::createUnit($period, null, 'Pengurus Inti', $nextRootUrutan++);
            $created++;

            self::attachAll($buckets['pengurus-inti'], $coreUnit);
        }

        // Rantai terkonfirmasi (bisa berubah kapan saja lewat Filament):
        // Pengasuh → Pembina Penasehat → Ketua Pembina.
        $pembinaUnit = null;

        if (isset($buckets['pembina-penasehat'])) {
            $pembinaUnit = self::createUnit(
                $period,
                $pengasuhUnit?->getKey(),
                'Pembina Penasehat',
                1,
            );
            $created++;

            self::attachAll($buckets['pembina-penasehat'], $pembinaUnit);

            if (isset($buckets['ketua-pembina'])) {
                $ketuaUnit = self::createUnit(
                    $period,
                    $pembinaUnit->getKey(),
                    'Ketua Pembina',
                    1,
                );
                $created++;

                self::attachAll($buckets['ketua-pembina'], $ketuaUnit);
            }
        } elseif (isset($buckets['ketua-pembina'])) {
            $ketuaUnit = self::createUnit($period, null, 'Ketua Pembina', $nextRootUrutan++);
            $created++;

            self::attachAll($buckets['ketua-pembina'], $ketuaUnit);
        }

        $bidangRoot = null;

        if ($bidangOrder !== []) {
            $bidangRoot = self::createUnit($period, null, 'Bidang Kepengurusan', $nextRootUrutan);
            $created++;

            self::attachEmpty($bidangRoot);

            $childUrutan = 1;

            foreach ($bidangOrder as [$key, $bucketName]) {
                $child = self::createUnit($period, $bidangRoot->getKey(), $bucketName, $childUrutan++);
                $created++;

                self::attachAll($buckets[$key], $child);
            }
        }

        // Unit induk tanpa anggota (mis. wadah "Pengasuh" kosong saat jabatan
        // pengasuh tak ada) tetap sah: pohon tak melarang wadah kosong.

        return [
            'period' => (string) $period->label,
            'mode' => 'mapped',
            'units' => $created,
            'members' => count($members),
        ];
    }

    /**
     * @param  ManagementMember[]  $members
     * @return array{period: string, mode: string, units: int, members: int}
     */
    private static function backfillSingleContainer(ManagementPeriod $period, array $members): array
    {
        $container = self::createUnit($period, null, 'Struktur '.$period->label, 1);

        self::attachAll($members, $container);

        return [
            'period' => (string) $period->label,
            'mode' => 'single-container',
            'units' => 1,
            'members' => count($members),
        ];
    }

    /**
     * Klasifikasi deterministik satu jabatan menjadi
     * [kunci bucket, nama unit], atau null bila tidak dikenali.
     *
     * @return array{0: string, 1: ?string}|null
     */
    private static function classify(string $jabatan): ?array
    {
        $lc = mb_strtolower(trim($jabatan));

        if (isset(self::EXACT_MAP[$lc])) {
            $key = self::EXACT_MAP[$lc];

            return [$key, str_starts_with($key, 'bidang:')
                ? (self::BIDANG_MAP[mb_substr($key, 7)] ?? null)
                : null];
        }

        foreach (self::PREFIXES as $prefix) {
            if (str_starts_with($lc, $prefix.' ')) {
                $remainder = trim(mb_substr($lc, mb_strlen($prefix.' ')));

                if (isset(self::BIDANG_MAP[$remainder])) {
                    return ['bidang:'.$remainder, self::BIDANG_MAP[$remainder]];
                }

                return null;
            }
        }

        return null;
    }

    private static function createUnit(
        ManagementPeriod $period,
        ?int $parentId,
        string $nama,
        int $urutan,
    ): ManagementUnit {
        /** @var ManagementUnit $unit */
        $unit = ManagementUnit::query()->create([
            'management_period_id' => (int) $period->getKey(),
            'parent_id' => $parentId,
            'nama' => $nama,
            'urutan' => $urutan,
        ]);

        return $unit;
    }

    /**
     * Rekatkan anggota ke unit. HANYA kolom management_unit_id yang
     * ditulisi — jabatan/nama/urutan/periode tidak tersentuh.
     *
     * @param  iterable<ManagementMember>  $members
     */
    private static function attachAll(iterable $members, ManagementUnit $unit): void
    {
        foreach ($members as $member) {
            $member->forceFill(['management_unit_id' => $unit->getKey()])->save();
        }
    }

    private static function attachEmpty(ManagementUnit $unit): void
    {
        // Wadah tanpa anggota tetap dibuat agar struktur utuh (Bidang induk).
    }
}
