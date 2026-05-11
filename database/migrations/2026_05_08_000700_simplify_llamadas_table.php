<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $obsoleteColumns = [
        'tipo',
        'fecha_evento',
        'campaign',
        'dst',
        'disposition',
        'userfield',
        'contact',
        'dialbase',
        'documento',
        'event',
        'callidnum',
        'guid',
        'queue',
        'enterdate',
        'posabandon',
        'posoriginal',
        'callerid',
        'timewait',
        'legacy_table',
        'legacy_id',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('llamadas')) {
            return;
        }

        $this->dropIndexIfExists('llamadas', 'llamadas_tipo_fecha_evento_index');
        $this->dropIndexIfExists('llamadas', 'llamadas_documento_index');
        $this->dropIndexIfExists('llamadas', 'llamadas_callerid_index');
        $this->dropIndexIfExists('llamadas', 'llamadas_legacy_unique');

        foreach ($this->obsoleteColumns as $column) {
            if (!Schema::hasColumn('llamadas', $column)) {
                continue;
            }

            Schema::table('llamadas', function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });
        }

        $this->addIndexIfMissing('llamadas', 'llamadas_dni_idx', ['dni']);
        $this->addIndexIfMissing('llamadas', 'llamadas_telefono_idx', ['telefono']);
        $this->addIndexIfMissing('llamadas', 'llamadas_fecha_gestion_idx', ['fecha_gestion']);
        $this->addIndexIfMissing('llamadas', 'llamadas_fuente_idx', ['fuente']);
        $this->addIndexIfMissing('llamadas', 'llamadas_resultado_idx', ['resultado']);
        $this->addUniqueIfMissing('llamadas', 'llamadas_unique_operational_call', ['fuente', 'dni', 'telefono', 'fecha_gestion', 'resultado_gestion']);
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

    private function addUniqueIfMissing(string $table, string $index, array $columns): void
    {
        if ($this->hasIndex($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($index, $columns) {
            $table->unique($columns, $index);
        });
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (!$this->hasIndex($table, $index)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($index) {
            $table->dropIndex($index);
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
