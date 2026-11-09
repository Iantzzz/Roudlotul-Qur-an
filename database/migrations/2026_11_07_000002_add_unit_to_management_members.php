<?php

use App\Support\ManagementBackfill;
use App\Support\ManagementStructure;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Anggota ditempel pada satu wadah (unit) milik periode yang sama.
     * Hapus unit TIDAK menghapus anggota: FK mengosongkan hanya kolom
     * management_unit_id (atau trigger pada PG < 15). Kolom jabatan,
     * nama, urutan, dan management_period_id TIDAK diubah.
     *
     * Backfill satu kali (guard ganda: tabel migrations + skip internal
     * bila periode sudah punya unit) memetakan data lama ke wadah
     * TANPA mengubah satu byte jabatan/nama/urutan/periode.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        Schema::table('management_members', function (Blueprint $table) {
            $table->foreignId('management_unit_id')->nullable();

            $table->index('management_unit_id', 'management_members_unit_id_index');
        });

        if ($driver === 'pgsql') {
            $version = ManagementStructure::postgresVersion();

            if ($version !== null && $version >= ManagementStructure::PG_MIN_COMPOSITE_FK) {
                // Composite FK: unit HARUS satu periode dengan anggota,
                // dan DELETE unit hanya men-null management_unit_id.
                DB::statement(<<<'SQL'
                    ALTER TABLE management_members
                      ADD CONSTRAINT management_members_unit_period_foreign
                      FOREIGN KEY (management_unit_id, management_period_id)
                      REFERENCES management_units (id, management_period_id)
                      ON DELETE SET NULL (management_unit_id)
                SQL);
            } else {
                DB::statement("ALTER TABLE management_members ADD CONSTRAINT management_members_management_unit_id_foreign FOREIGN KEY (management_unit_id) REFERENCES management_units (id) ON DELETE SET NULL");
                DB::statement(<<<'SQL'
                    CREATE OR REPLACE FUNCTION rlq_members_unit_period_guard() RETURNS trigger
                    LANGUAGE plpgsql
                    AS $$
                    BEGIN
                        IF NEW.management_unit_id IS NOT NULL THEN
                            IF NOT EXISTS (
                                SELECT 1 FROM management_units u
                                WHERE u.id = NEW.management_unit_id
                                  AND u.management_period_id = NEW.management_period_id
                            ) THEN
                                RAISE EXCEPTION 'Wadah/unit harus berasal dari periode yang sama dengan anggota.';
                            END IF;
                        END IF;

                        RETURN NEW;
                    END;
                    $$;
                SQL);
                DB::statement(<<<'SQL'
                    CREATE TRIGGER trg_management_members_unit_period
                    BEFORE INSERT OR UPDATE OF management_unit_id, management_period_id
                    ON management_members
                    FOR EACH ROW EXECUTE FUNCTION rlq_members_unit_period_guard()
                SQL);
            }
        } elseif ($driver === 'sqlite') {
            // SQLite: FK ditambahkan lewat rebuild tabel (didukung
            // framework); nullOnDelete memakai FK kolom tunggal.
            Schema::table('management_members', function (Blueprint $table) {
                $table->foreign('management_unit_id')
                    ->references('id')
                    ->on('management_units')
                    ->nullOnDelete();
            });
        } else {
            Schema::table('management_members', function (Blueprint $table) {
                $table->foreign('management_unit_id')
                    ->references('id')
                    ->on('management_units')
                    ->nullOnDelete();
            });
        }

        ManagementBackfill::run();
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP TRIGGER IF EXISTS trg_management_members_unit_period ON management_members');
            DB::statement('DROP FUNCTION IF EXISTS rlq_members_unit_period_guard()');
            DB::statement('ALTER TABLE management_members DROP CONSTRAINT IF EXISTS management_members_unit_period_foreign');
            DB::statement('ALTER TABLE management_members DROP CONSTRAINT IF EXISTS management_members_management_unit_id_foreign');
        }

        Schema::table('management_members', function (Blueprint $table) {
            $table->dropIndex('management_members_unit_id_index');
            $table->dropColumn('management_unit_id');
        });
    }
};
