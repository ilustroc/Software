<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillLegacyLlamadasCommand extends Command
{
    protected $signature = 'llamadas:backfill-legacy {--chunk=1000 : Cantidad de filas por bloque}';

    protected $description = 'Unifica Llamadas_Abandonadas, Llamadas_AMD y Llamadas_IVR en la tabla llamadas.';

    private const RESULTADO = 'NO CONTACTO';

    public function handle(): int
    {
        $this->disableQueryLogs();

        if (!Schema::hasTable('llamadas')) {
            $this->error('No existe la tabla llamadas. Ejecuta primero php artisan migrate.');

            return self::FAILURE;
        }

        $chunkSize = max(1, (int) $this->option('chunk'));
        $totalRead = 0;
        $totalValid = 0;
        $totalInserted = 0;

        foreach ($this->sources() as $source) {
            if (!Schema::hasTable($source['table'])) {
                $this->warn("Tabla {$source['table']} no existe. Se omite.");
                continue;
            }

            $this->info("Procesando {$source['table']} en bloques de {$chunkSize}...");

            $sourceRead = 0;
            $sourceValid = 0;
            $sourceInserted = 0;

            DB::table($source['table'])
                ->orderBy('id')
                ->chunkById($chunkSize, function (Collection $rows) use ($source, &$sourceRead, &$sourceValid, &$sourceInserted) {
                    $now = now();
                    $payload = [];
                    $sourceRead += $rows->count();

                    foreach ($rows as $row) {
                        $mapped = $this->mapRow($source, (array) $row, $now);

                        if ($mapped === null) {
                            continue;
                        }

                        $payload[] = $mapped;
                    }

                    if ($payload === []) {
                        $this->printProgress($source['table'], $sourceRead, $sourceValid, $sourceInserted);
                        return;
                    }

                    $sourceValid += count($payload);
                    $sourceInserted += DB::table('llamadas')->insertOrIgnore($payload);

                    unset($payload, $rows);
                    gc_collect_cycles();

                    $this->printProgress($source['table'], $sourceRead, $sourceValid, $sourceInserted);
                }, 'id');

            $this->info("{$source['table']} listo. Leidos: {$sourceRead}. Validos: {$sourceValid}. Insertados nuevos: {$sourceInserted}.");

            $totalRead += $sourceRead;
            $totalValid += $sourceValid;
            $totalInserted += $sourceInserted;
        }

        $this->info("Backfill terminado. Leidos: {$totalRead}. Validos: {$totalValid}. Insertados nuevos: {$totalInserted}.");

        return self::SUCCESS;
    }

    private function disableQueryLogs(): void
    {
        DB::connection('mysql')->disableQueryLog();
    }
    
    private function printProgress(string $table, int $read, int $valid, int $inserted): void
    {
        if ($read % 50000 !== 0) {
            return;
        }

        $this->line("  {$table}: {$read} leidos, {$valid} validos, {$inserted} insertados.");
    }

    private function sources(): array
    {
        return [
            [
                'table' => 'Llamadas_Abandonadas',
                'dni' => 'documento',
                'telefono' => 'callerid',
                'resultado_gestion' => 'event',
                'fecha_gestion' => 'enterdate',
                'metadata_exclude' => ['id', 'documento', 'callerid', 'event', 'enterdate'],
            ],
            [
                'table' => 'Llamadas_AMD',
                'dni' => 'doc',
                'telefono' => 'dst',
                'resultado_gestion' => 'disposition',
                'fecha_gestion' => 'calldate',
                'metadata_exclude' => ['id', 'doc', 'dst', 'disposition', 'calldate'],
            ],
            [
                'table' => 'Llamadas_IVR',
                'dni' => 'doc',
                'telefono' => 'dst',
                'resultado_gestion' => 'disposition',
                'fecha_gestion' => 'calldate',
                'metadata_exclude' => ['id', 'doc', 'dst', 'disposition', 'calldate'],
            ],
        ];
    }

    private function mapRow(array $source, array $row, \DateTimeInterface $now): ?array
    {
        $dni = trim((string) ($row[$source['dni']] ?? ''));

        if ($dni === '' || !ctype_digit($dni)) {
            return null;
        }

        $telefono = $this->textValue($row[$source['telefono']] ?? null, 40);
        $resultadoGestion = $this->textValue($row[$source['resultado_gestion']] ?? null, 150);
        $fechaGestion = $this->parseDate($row[$source['fecha_gestion']] ?? null);

        if ($fechaGestion === null) {
            return null;
        }

        $metadata = $this->metadata($row, $source['metadata_exclude']);

        return [
            'dni' => $dni,
            'telefono' => $telefono,
            'resultado_gestion' => $resultadoGestion,
            'resultado' => self::RESULTADO,
            'fecha_gestion' => $fechaGestion,
            'observaciones' => null,
            'fuente' => $source['table'],
            'metadata' => $metadata,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function metadata(array $row, array $exclude): ?string
    {
        $metadata = array_diff_key($row, array_flip($exclude));

        if ($metadata === []) {
            return null;
        }

        return json_encode($metadata, JSON_UNESCAPED_UNICODE);
    }

    private function textValue(mixed $value, int $limit): string
    {
        $value = trim((string) $value);

        return mb_substr($value, 0, $limit);
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $timestamp = strtotime(str_replace('/', '-', $value));

        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }
}
