<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $obsoleteColumns = [
        'licencia_id',
        'socio',
        'cliente',
        'tipificacion',
        'resultado',
        'asesor',
        'entidad',
        'subcartera',
        'fecha_gestion',
        'fecha_agenda',
        'telefono',
        'comentario',
        'nro_cuotas',
        'metadata',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('gestiones')) {
            return;
        }

        $this->addNewColumns();
        $this->copyExistingValues();

        $this->addIndexIfMissing('gestiones', 'gestiones_cartera_id_index', ['cartera_id']);

        $this->dropIndexIfExists('gestiones', 'gestiones_cartera_id_fecha_gestion_index');
        $this->dropIndexIfExists('gestiones', 'gestiones_telefono_index');
        $this->dropIndexIfExists('gestiones', 'gestiones_tipificacion_index');

        foreach ($this->obsoleteColumns as $column) {
            if (!Schema::hasColumn('gestiones', $column)) {
                continue;
            }

            Schema::table('gestiones', function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });
        }

        $this->addIndexIfMissing('gestiones', 'gestiones_documento_index', ['documento']);
        $this->addIndexIfMissing('gestiones', 'gestiones_operacion_index', ['operacion']);
        $this->addIndexIfMissing('gestiones', 'gestiones_dateprocessed_index', ['dateprocessed']);
        $this->addIndexIfMissing('gestiones', 'gestiones_callerid_index', ['callerid']);
        $this->addIndexIfMissing('gestiones', 'gestiones_value2_index', ['value2']);
        $this->addIndexIfMissing('gestiones', 'gestiones_origen_index', ['origen']);
        $this->addUniqueIfMissing('gestiones', 'gestiones_origen_legacy_id_unique', ['origen', 'legacy_id']);
    }

    public function down(): void
    {
        // No-op: keep management data safe in shared/production databases.
    }

    private function addNewColumns(): void
    {
        Schema::table('gestiones', function (Blueprint $table) {
            if (!Schema::hasColumn('gestiones', 'nombre')) {
                $table->string('nombre', 200)->nullable()->after('documento');
            }

            if (!Schema::hasColumn('gestiones', 'value2')) {
                $table->string('value2', 300)->nullable()->after('nombre');
            }

            if (!Schema::hasColumn('gestiones', 'value1')) {
                $table->string('value1', 300)->nullable()->after('value2');
            }

            if (!Schema::hasColumn('gestiones', 'fullname')) {
                $table->string('fullname', 150)->nullable()->after('value1');
            }

            if (!Schema::hasColumn('gestiones', 'dateprocessed')) {
                $table->dateTime('dateprocessed')->nullable()->after('operacion');
            }

            if (!Schema::hasColumn('gestiones', 'callerid')) {
                $table->string('callerid', 200)->nullable()->after('dateprocessed');
            }

            if (!Schema::hasColumn('gestiones', 'comment')) {
                $table->text('comment')->nullable()->after('callerid');
            }

            if (!Schema::hasColumn('gestiones', 'nro_cuota')) {
                $table->string('nro_cuota', 50)->nullable()->after('monto_promesa');
            }

            if (!Schema::hasColumn('gestiones', 'legacy_id')) {
                $table->string('legacy_id', 80)->nullable()->after('origen');
            }
        });
    }

    private function copyExistingValues(): void
    {
        $updates = [];

        if (Schema::hasColumn('gestiones', 'cliente') || Schema::hasColumn('gestiones', 'socio')) {
            $updates[] = '`nombre` = COALESCE(`nombre`, ' . $this->nullableColumn('cliente') . ', ' . $this->nullableColumn('socio') . ')';
        }

        if (Schema::hasColumn('gestiones', 'tipificacion')) {
            $updates[] = '`value2` = COALESCE(`value2`, `tipificacion`)';
        }

        if (Schema::hasColumn('gestiones', 'resultado')) {
            $updates[] = '`value1` = COALESCE(`value1`, `resultado`)';
        }

        if (Schema::hasColumn('gestiones', 'asesor')) {
            $updates[] = '`fullname` = COALESCE(`fullname`, `asesor`)';
        }

        if (Schema::hasColumn('gestiones', 'fecha_gestion')) {
            $updates[] = '`dateprocessed` = COALESCE(`dateprocessed`, `fecha_gestion`)';
        }

        if (Schema::hasColumn('gestiones', 'telefono')) {
            $updates[] = '`callerid` = COALESCE(`callerid`, `telefono`)';
        }

        if (Schema::hasColumn('gestiones', 'comentario')) {
            $updates[] = '`comment` = COALESCE(`comment`, `comentario`)';
        }

        if (Schema::hasColumn('gestiones', 'nro_cuotas')) {
            $updates[] = '`nro_cuota` = COALESCE(`nro_cuota`, CAST(`nro_cuotas` AS CHAR))';
        }

        if ($updates === []) {
            return;
        }

        DB::statement('UPDATE gestiones SET ' . implode(', ', $updates));
    }

    private function nullableColumn(string $column): string
    {
        return Schema::hasColumn('gestiones', $column) ? "`{$column}`" : 'NULL';
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
