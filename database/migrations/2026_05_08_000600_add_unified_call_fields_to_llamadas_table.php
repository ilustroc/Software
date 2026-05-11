<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('llamadas')) {
            return;
        }

        Schema::table('llamadas', function (Blueprint $table) {
            if (!Schema::hasColumn('llamadas', 'dni')) {
                $table->string('dni', 30)->nullable();
            }

            if (!Schema::hasColumn('llamadas', 'telefono')) {
                $table->string('telefono', 40)->nullable();
            }

            if (!Schema::hasColumn('llamadas', 'resultado_gestion')) {
                $table->string('resultado_gestion', 150)->nullable();
            }

            if (!Schema::hasColumn('llamadas', 'resultado')) {
                $table->string('resultado', 120)->nullable();
            }

            if (!Schema::hasColumn('llamadas', 'fecha_gestion')) {
                $table->dateTime('fecha_gestion')->nullable();
            }

            if (!Schema::hasColumn('llamadas', 'observaciones')) {
                $table->text('observaciones')->nullable();
            }

            if (!Schema::hasColumn('llamadas', 'fuente')) {
                $table->string('fuente', 80)->nullable();
            }

            if (!Schema::hasColumn('llamadas', 'metadata')) {
                $table->json('metadata')->nullable();
            }

            if (!Schema::hasColumn('llamadas', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (!Schema::hasColumn('llamadas', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        $this->addIndexIfMissing('llamadas', 'llamadas_dni_idx', ['dni']);
        $this->addIndexIfMissing('llamadas', 'llamadas_telefono_idx', ['telefono']);
        $this->addIndexIfMissing('llamadas', 'llamadas_fecha_gestion_idx', ['fecha_gestion']);
        $this->addIndexIfMissing('llamadas', 'llamadas_fuente_idx', ['fuente']);
        $this->addIndexIfMissing('llamadas', 'llamadas_resultado_idx', ['resultado']);
    }

    public function down(): void
    {
        // No-op: keep call data safe in shared/production databases.
    }

    private function addIndexIfMissing(string $table, string $index, array $columns): void
    {
        if ($this->hasIndex($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($index, $columns) {
            $table->index($columns, $index);
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('{$table}')"))
                ->contains(fn (object $row) => ($row->name ?? null) === $index);
        }

        if ($driver === 'mysql') {
            return DB::table('information_schema.statistics')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $table)
                ->where('index_name', $index)
                ->exists();
        }

        return false;
    }
};
