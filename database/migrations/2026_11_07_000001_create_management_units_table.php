<?php

use App\Support\ManagementStructure;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pohon unit kepengurusan per periode. Parent unit hanya boleh dari
     * periode yang sama; penghapusan unit induk TIDAK memusnahkan turunan
     * (SET NULL pada parent saja), dan penghapusan unit TIDAK menyentuh
     * periode (FK taraf only cascade ke periode).
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        Schema::create('management_units', function (Blueprint $table) use ($driver) {
            $table->id();

            // Unit adalah milik satu periode; menghapus periode
            // menghapus struktur periode itu saja.
            $table->foreignId('management_period_id')
                ->constrained('management_periods')
                ->cascadeOnDelete();

            // Ditambahkan FK eksplisit di bawah sesuai kemampuan driver:
            // parent yang hilang mengosongkan parent_id saja.
            $table->foreignId('parent_id')->nullable();

            $table->string('nama')->comment('Nama bebas: kelompok, bidang, atau jabatan.');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            // Target referensi composite FK ("multitenant trick":
            // id + period harus unik agar satu baris mengidentifikasi pasangan).
            $table->unique(['id', 'management_period_id'], 'management_units_period_unique');

            $table->index('parent_id', 'management_units_parent_id_index');
            $table->index('management_period_id', 'management_units_period_id_index');

            if ($driver === 'sqlite') {
                // SQLite menegakkan FK saat foreign_keys=ON; nullOnDelete
                // mengosongkan hanya kolom parent_id (single-column FK).
                $table->foreign('parent_id')
                    ->references('id')
                    ->on('management_units')
                    ->nullOnDelete();
            }
        });

        if ($driver === 'pgsql') {
            $version = ManagementStructure::postgresVersion();

            if ($version !== null && $version >= ManagementStructure::PG_MIN_COMPOSITE_FK) {
                // PostgreSQL 15+: integritas satu-periode DIBATASI DATABASE,
                // dengan nullOnDelete yang hanya menyentuh parent_id.
                DB::statement(<<<'SQL'
                    ALTER TABLE management_units
                      ADD CONSTRAINT management_units_parent_period_foreign
                      FOREIGN KEY (parent_id, management_period_id)
                      REFERENCES management_units (id, management_period_id)
                      ON DELETE SET NULL (parent_id)
                SQL);
            } else {
                // PostgreSQL < 15: FK keberadaan (SET NULL kolom tunggal) +
                // trigger penjaga satu-periode — jaminan DB-level tetap utuh.
                DB::statement("ALTER TABLE management_units ADD CONSTRAINT management_units_parent_id_foreign FOREIGN KEY (parent_id) REFERENCES management_units (id) ON DELETE SET NULL");
                DB::statement(<<<'SQL'
                    CREATE OR REPLACE FUNCTION rlq_units_parent_period_guard() RETURNS trigger
                    LANGUAGE plpgsql
                    AS $$
                    BEGIN
                        IF NEW.parent_id IS NOT NULL THEN
                            IF NOT EXISTS (
                                SELECT 1 FROM management_units p
                                WHERE p.id = NEW.parent_id
                                  AND p.management_period_id = NEW.management_period_id
                            ) THEN
                                RAISE EXCEPTION 'Unit induk harus berasal dari periode yang sama (unit %).', NEW.id;
                            END IF;
                        END IF;

                        RETURN NEW;
                    END;
                    $$;
                SQL);
                DB::statement(<<<'SQL'
                    CREATE TRIGGER trg_management_units_parent_period
                    BEFORE INSERT OR UPDATE OF parent_id, management_period_id
                    ON management_units
                    FOR EACH ROW EXECUTE FUNCTION rlq_units_parent_period_guard()
                SQL);
            }
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP TRIGGER IF EXISTS trg_management_units_parent_period ON management_units');
            DB::statement('DROP FUNCTION IF EXISTS rlq_units_parent_period_guard()');
            DB::statement('ALTER TABLE management_units DROP CONSTRAINT IF EXISTS management_units_parent_period_foreign');
            DB::statement('ALTER TABLE management_units DROP CONSTRAINT IF EXISTS management_units_parent_id_foreign');
        }

        Schema::dropIfExists('management_units');
    }
};
