<?php

namespace App\Console\Commands;

use App\Models\Cartera;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillLegacyGestionesCommand extends Command
{
    protected $signature = 'gestiones:backfill-legacy {--chunk=1000 : Cantidad de filas por bloque}';

    protected $description = 'Unifica Gestiones_1y2, Gestiones_Propia3, Gestiones_Propia4 y Gestiones_APDAYC en la tabla gestiones.';

    public function handle(): int
    {
        $this->disableQueryLogs();

        if (!Schema::hasTable('gestiones')) {
            $this->error('No existe la tabla gestiones. Ejecuta primero php artisan migrate.');

            return self::FAILURE;
        }

        $sources = $this->sourcesWithCarteras();

        if ($sources === null) {
            return self::FAILURE;
        }

        $chunkSize = max(1, (int) $this->option('chunk'));
        $totals = [
            'read' => 0,
            'inserted' => 0,
            'duplicates' => 0,
            'invalid_document' => 0,
        ];

        foreach ($sources as $source) {
            $stats = $this->processSource($source, $chunkSize);

            foreach ($totals as $key => $value) {
                $totals[$key] = $value + $stats[$key];
            }
        }

        $this->newLine();
        $this->info('Resumen final');
        $this->table(
            ['Leidos', 'Insertados', 'Duplicados', 'Documento invalido'],
            [[$totals['read'], $totals['inserted'], $totals['duplicates'], $totals['invalid_document']]],
        );

        return self::SUCCESS;
    }

    private function processSource(array $source, int $chunkSize): array
    {
        $stats = [
            'read' => 0,
            'inserted' => 0,
            'duplicates' => 0,
            'invalid_document' => 0,
        ];

        if (!Schema::hasTable($source['table'])) {
            $this->warn("Tabla {$source['table']} no existe. Se omite.");

            return $stats;
        }

        $hasId = Schema::hasColumn($source['table'], 'id');
        $this->info("Procesando {$source['table']} en bloques de {$chunkSize}...");

        $callback = function (Collection $rows) use ($source, &$stats) {
            $now = now();
            $payload = [];
            $previousRead = $stats['read'];
            $stats['read'] += $rows->count();

            foreach ($rows as $row) {
                $mapped = $this->mapRow($source, (array) $row, $now);

                if ($mapped === null) {
                    $stats['invalid_document']++;
                    continue;
                }

                $payload[] = $mapped;

                if (count($payload) >= 250) {
                    $this->insertPayload($payload, $stats);
                    $payload = [];
                }
            }

            if ($payload !== []) {
                $this->insertPayload($payload, $stats);
            }

            unset($payload, $rows);
            gc_collect_cycles();

            if (intdiv($previousRead, 50000) !== intdiv($stats['read'], 50000)) {
                $this->line("  {$source['table']}: {$stats['read']} leidos, {$stats['inserted']} insertados, {$stats['duplicates']} duplicados, {$stats['invalid_document']} documentos invalidos.");
            }
        };

        if ($hasId) {
            DB::table($source['table'])
                ->orderBy('id')
                ->chunkById($chunkSize, $callback, 'id');
        } else {
            DB::table($source['table'])
                ->orderBy($source['documento'])
                ->orderBy($source['dateprocessed'])
                ->orderBy($source['operacion'] ?? $source['documento'])
                ->chunk($chunkSize, $callback);
        }

        $this->table(
            ['Tabla', 'Leidos', 'Insertados', 'Duplicados', 'Documento invalido'],
            [[$source['table'], $stats['read'], $stats['inserted'], $stats['duplicates'], $stats['invalid_document']]],
        );

        return $stats;
    }

    private function insertPayload(array $payload, array &$stats): void
    {
        $origen = $payload[0]['origen'] ?? null;
        $legacyIds = array_values(array_unique(array_column($payload, 'legacy_id')));

        if ($origen !== null && $legacyIds !== []) {
            $existing = DB::table('gestiones')
                ->where('origen', $origen)
                ->whereIn('legacy_id', $legacyIds)
                ->pluck('legacy_id')
                ->map(fn ($legacyId) => (string) $legacyId)
                ->all();

            if ($existing !== []) {
                $existing = array_flip($existing);
                $payload = array_values(array_filter(
                    $payload,
                    fn (array $row) => !isset($existing[(string) $row['legacy_id']]),
                ));
            }
        }

        if ($payload === []) {
            $stats['duplicates'] += count($legacyIds);
            return;
        }

        $attempted = count($payload);
        $inserted = DB::table('gestiones')->insertOrIgnore($payload);
        $stats['inserted'] += $inserted;
        $stats['duplicates'] += count($legacyIds) - $attempted + ($attempted - $inserted);
    }

    private function sourcesWithCarteras(): ?array
    {
        $sources = $this->sources();

        foreach ($sources as &$source) {
            $carteraId = $this->findCarteraId($source['cartera_slug'], $source['cartera_nombre']);

            if ($carteraId === null) {
                $this->error("No existe la cartera {$source['cartera_nombre']} ({$source['cartera_slug']}).");

                return null;
            }

            $source['cartera_id'] = $carteraId;
        }

        return $sources;
    }

    private function sources(): array
    {
        return [
            [
                'table' => 'Gestiones_1y2',
                'cartera_slug' => 'propia12',
                'cartera_nombre' => 'Propia 1 y 2',
                'documento' => 'documento',
                'nombre' => 'nombre',
                'value2' => 'value2',
                'value1' => 'value1',
                'fullname' => 'fullname',
                'operacion' => 'operacion',
                'dateprocessed' => 'dateprocessed',
                'callerid' => 'callerid',
                'comment' => 'comment',
                'monto_promesa' => 'pagar_por_cuota',
                'nro_cuota' => 'nrocuotas',
                'fecha_promesa' => 'fecha_promesa',
                'campaign' => 'campaign',
            ],
            [
                'table' => 'Gestiones_Propia3',
                'cartera_slug' => 'propia3',
                'cartera_nombre' => 'Propia 3',
                'documento' => 'documento',
                'nombre' => 'nombre',
                'value2' => 'value2',
                'value1' => 'value1',
                'fullname' => 'fullname',
                'operacion' => 'operacion',
                'dateprocessed' => 'dateprocessed',
                'callerid' => 'callerid',
                'comment' => 'comment',
                'monto_promesa' => 'pagar_por_cuota',
                'nro_cuota' => 'nrocuotas',
                'fecha_promesa' => 'fecha_promesa',
                'campaign' => 'campaign',
            ],
            [
                'table' => 'Gestiones_Propia4',
                'cartera_slug' => 'kp-invest',
                'cartera_nombre' => 'KP Invest',
                'documento' => 'documento',
                'nombre' => 'cliente',
                'value2' => 'value2',
                'value1' => 'value1',
                'fullname' => 'fullname',
                'operacion' => 'operacion',
                'dateprocessed' => 'dateprocessed',
                'callerid' => 'callerid',
                'comment' => 'comment',
                'monto_promesa' => 'importe_financiamiento',
                'nro_cuota' => 'nrocuotas',
                'fecha_promesa' => 'fecha_promesa',
                'campaign' => 'campaign',
            ],
            [
                'table' => 'Gestiones_APDAYC',
                'cartera_slug' => 'apdayc',
                'cartera_nombre' => 'APDAYC',
                'documento' => 'documento',
                'nombre' => 'socio',
                'value2' => 'value2',
                'value1' => 'value1',
                'fullname' => 'fullname',
                'operacion' => 'lic_id',
                'dateprocessed' => 'dateprocessed',
                'callerid' => 'callerid',
                'comment' => 'comment',
                'monto_promesa' => 'montopromesa',
                'nro_cuota' => 'nrocuota',
                'fecha_promesa' => 'fecha_promesa',
                'campaign' => 'campaign',
            ],
        ];
    }

    private function mapRow(array $source, array $row, \DateTimeInterface $now): ?array
    {
        $row = array_change_key_case($row, CASE_LOWER);
        $documento = trim((string) ($row[$source['documento']] ?? ''));

        if ($documento === '' || !ctype_digit($documento)) {
            return null;
        }

        return [
            'cartera_id' => $source['cartera_id'],
            'documento' => $this->textValue($documento, 30),
            'nombre' => $this->textValue($row[$source['nombre']] ?? null, 200),
            'value2' => $this->textValue($row[$source['value2']] ?? null, 300),
            'value1' => $this->textValue($row[$source['value1']] ?? null, 300),
            'fullname' => $this->textValue($row[$source['fullname']] ?? null, 150),
            'operacion' => $this->textValue($row[$source['operacion']] ?? null, 100),
            'dateprocessed' => $this->parseDate($row[$source['dateprocessed']] ?? null),
            'callerid' => $this->textValue($row[$source['callerid']] ?? null, 200),
            'comment' => $this->textValue($row[$source['comment']] ?? null),
            'monto_promesa' => $this->parseMonto($row[$source['monto_promesa']] ?? null),
            'nro_cuota' => $this->textValue($row[$source['nro_cuota']] ?? null, 50),
            'fecha_promesa' => $this->parseDate($row[$source['fecha_promesa']] ?? null),
            'campaign' => $this->textValue($row[$source['campaign']] ?? null, 120),
            'origen' => $source['table'],
            'legacy_id' => $this->legacyId($source, $row),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function legacyId(array $source, array $row): string
    {
        if (array_key_exists('id', $row) && $row['id'] !== null && $row['id'] !== '') {
            return (string) $row['id'];
        }

        return hash('sha256', implode('|', [
            $row[$source['documento']] ?? '',
            $row[$source['operacion']] ?? '',
            $row[$source['dateprocessed']] ?? '',
            $row[$source['callerid']] ?? '',
            $row[$source['comment']] ?? '',
            $row[$source['monto_promesa']] ?? '',
            $row[$source['fecha_promesa']] ?? '',
            $row[$source['campaign']] ?? '',
        ]));
    }

    private function findCarteraId(string $slug, string $nombre): ?int
    {
        return Cartera::query()
            ->where('slug', $slug)
            ->orWhere('nombre', $nombre)
            ->value('id');
    }

    private function disableQueryLogs(): void
    {
        DB::connection()->disableQueryLog();
    }

    private function textValue(mixed $value, ?int $limit = null): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return $limit ? mb_substr($value, 0, $limit) : $value;
    }

    private function parseMonto(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = preg_replace('/[^\d,.\-]/', '', (string) $value);

        if ($clean === '') {
            return null;
        }

        if (str_contains($clean, ',') && str_contains($clean, '.')) {
            $clean = strrpos($clean, ',') > strrpos($clean, '.')
                ? str_replace(',', '.', str_replace('.', '', $clean))
                : str_replace(',', '', $clean);
        } else {
            $clean = str_replace(',', '.', $clean);
        }

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        $value = trim((string) $value);

        if ($value === '' || stripos($value, 'invalida') !== false) {
            return null;
        }

        $timestamp = strtotime(str_replace('/', '-', $value));

        return $timestamp === false ? null : date('Y-m-d H:i:s', $timestamp);
    }
}
