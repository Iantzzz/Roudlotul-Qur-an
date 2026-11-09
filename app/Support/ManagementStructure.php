<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Konstanta bersama untuk seluruh sistem Struktur Kepengurusan.
 *
 * MAX_DEPTH adalah SATU-SATUNYA sumber batas kedalaman: dipakai oleh
 * validasi model (ManagementUnit), builder pohon (ManagementTree),
 * dan seluruh pemakai components rekursif/CSS (org-branch--depth-N).
 * Kedalaman melebihi batas DITOLAK secara eksplisit saat penyimpanan
 * (ValidationException), bukan dibuang/dihilangkan saat render.
 */
final class ManagementStructure
{
    /**
     * Batas maksimum kedalaman pohon (root = 1).
     * Dipetakan 1:1 ke kelas CSS .org-branch--depth-1..6 di public/css/site.css.
     */
    public const MAX_DEPTH = 6;

    /**
     * Versi PostgreSQL pelanggan untuk ON DELETE SET NULL (kolom_list)
     * (masuk ke PostgreSQL 15, commit d6f96ed Des 2021).
     */
    public const PG_MIN_COMPOSITE_FK = 150000;

    /**
     * Ambil versi PostgreSQL server (angka, mis. 160009), atau null bila
     * bukan pgsql / query gagal. Tidak pernah melempar exception.
     */
    public static function postgresVersion(): ?int
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return null;
        }

        try {
            $row = DB::selectOne(
                "SELECT setting FROM pg_settings WHERE name = 'server_version_num'",
            );

            if ($row === null) {
                return null;
            }

            return (int) $row->setting;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Apakah driver saat ini mendukung composite FK dengan
     * ON DELETE SET NULL (column_list)?
     */
    public static function supportsCompositeSetNull(?int $postgresVersion = null): bool
    {
        $version = $postgresVersion ?? static::postgresVersion();

        return $version !== null && $version >= static::PG_MIN_COMPOSITE_FK;
    }
}
