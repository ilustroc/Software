<?php

namespace App\Services;

use App\Models\Gestion;
use App\Models\Llamada;
use Illuminate\Support\Facades\DB;

class GestionService
{
    public function __construct(private CarteraService $carteras)
    {
    }

    private function getMetadata(string $tipo): array
    {
        return match ($tipo) {
            'propia12' => ['sp' => 'spGestionPropia', 'kind' => 'gestion', 'cartera' => 'propia12'],
            'propia3' => ['sp' => 'spGestionZigor', 'kind' => 'gestion', 'cartera' => 'propia3'],
            'kpi', 'kp-invest', 'propia4' => ['sp' => 'spGestionKpi', 'kind' => 'gestion', 'cartera' => 'kp-invest'],
            'apdayc' => ['sp' => 'spGestionApdayc', 'kind' => 'gestion', 'cartera' => 'apdayc'],
            'amd' => ['sp' => 'spLlamadasAMD', 'kind' => 'llamada', 'tipo' => 'amd'],
            'ivr' => ['sp' => 'spLlamadasIVR', 'kind' => 'llamada', 'tipo' => 'ivr'],
            'abandonados' => ['sp' => 'spLlamadasAbandonadas', 'kind' => 'llamada', 'tipo' => 'abandonados'],
            default => throw new \InvalidArgumentException("Tipo de gestion [$tipo] no soportado."),
        };
    }

    public function sincronizar(string $tipo, string $desde, string $hasta): int
    {
        $meta = $this->getMetadata($tipo);
        $desdeFull = str_contains($desde, ':') ? $desde : $desde . ' 00:00:00';
        $hastaFull = str_contains($hasta, ':') ? $hasta : $hasta . ' 23:59:59';

        $rows = DB::connection('crm')->select("CALL {$meta['sp']}(?, ?)", [$desdeFull, $hastaFull]);

        if (empty($rows)) {
            return 0;
        }

        if (($meta['tipo'] ?? null) === 'amd') {
            $rows = array_values(array_filter($rows, function ($row) {
                $data = array_change_key_case((array) $row, CASE_LOWER);
                $campaign = strtoupper(trim((string) ($data['campaign'] ?? '')));

                return !str_starts_with($campaign, 'IVR_');
            }));
        }

        if (empty($rows)) {
            return 0;
        }

        return $meta['kind'] === 'llamada'
            ? $this->guardarLlamadas($meta['tipo'], $rows, $desdeFull, $hastaFull)
            : $this->guardarGestiones($meta['cartera'], $rows, $desdeFull, $hastaFull);
    }

    private function guardarGestiones(string $carteraSlug, array $rows, string $desde, string $hasta): int
    {
        $cartera = $this->carteras->findBySlugOrFail($carteraSlug);
        $now = now();
        $data = array_map(function ($row) use ($cartera, $carteraSlug, $now) {
            $source = array_change_key_case((array) $row, CASE_LOWER);
            $monto = $this->firstValue($source, 'pagar_por_cuota', 'importecuota', 'importe_financiamiento', 'importefinanciamiento', 'montopromesa');

            return [
                'cartera_id' => $cartera->id,
                'documento' => $this->firstValue($source, 'documento'),
                'nombre' => $this->nombreGestion($carteraSlug, $source),
                'value2' => $this->firstValue($source, 'value2'),
                'value1' => $this->firstValue($source, 'value1'),
                'fullname' => $this->firstValue($source, 'fullname'),
                'operacion' => $carteraSlug === 'apdayc'
                    ? $this->firstValue($source, 'lic_id')
                    : $this->firstValue($source, 'operacion'),
                'dateprocessed' => $this->parseFecha($this->firstValue($source, 'dateprocessed')),
                'callerid' => $this->firstValue($source, 'callerid'),
                'comment' => $this->firstValue($source, 'comment'),
                'monto_promesa' => $this->parseMonto($monto),
                'nro_cuota' => $this->firstValue($source, 'nrocuotas', 'nrocuota'),
                'fecha_promesa' => $this->parseFecha($this->firstValue($source, 'fecha_promesa', 'fechapromesa')),
                'campaign' => $this->firstValue($source, 'campaign'),
                'origen' => 'crm',
                'legacy_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $rows);

        return DB::transaction(function () use ($cartera, $data, $desde, $hasta) {
            Gestion::query()
                ->whereBelongsTo($cartera)
                ->whereBetween('dateprocessed', [$desde, $hasta])
                ->delete();

            foreach (array_chunk($data, 500) as $chunk) {
                Gestion::query()->insert($chunk);
            }

            return count($data);
        });
    }

    private function guardarLlamadas(string $tipo, array $rows, string $desde, string $hasta): int
    {
        $now = now();
        $fuente = $this->fuenteLlamada($tipo);
        $data = [];

        foreach ($rows as $row) {
            $source = array_change_key_case((array) $row, CASE_LOWER);
            $dni = trim((string) $this->firstValue($source, 'documento', 'doc'));

            if ($dni === '' || !ctype_digit($dni)) {
                continue;
            }

            if ($tipo === 'abandonados') {
                $fechaGestion = $this->parseFecha($this->firstValue($source, 'enterdate', 'datetime', 'fecha_evento'));
                $telefono = $this->firstValue($source, 'callerid');
                $resultadoGestion = $this->firstValue($source, 'event');

                if ($fechaGestion === null) {
                    continue;
                }

                $data[] = [
                    'dni' => $dni,
                    'telefono' => $this->normalizeCallText($telefono, 40),
                    'resultado_gestion' => $this->normalizeCallText($resultadoGestion, 150),
                    'resultado' => 'NO CONTACTO',
                    'fecha_gestion' => $fechaGestion,
                    'observaciones' => null,
                    'fuente' => $fuente,
                    'metadata' => json_encode($source, JSON_UNESCAPED_UNICODE),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                continue;
            }

            $fechaGestion = $this->parseFecha($this->firstValue($source, 'calldate', 'fecha_evento'));
            $telefono = $this->firstValue($source, 'dst');
            $resultadoGestion = $this->firstValue($source, 'disposition');

            if ($fechaGestion === null) {
                continue;
            }

            $data[] = [
                'dni' => $dni,
                'telefono' => $this->normalizeCallText($telefono, 40),
                'resultado_gestion' => $this->normalizeCallText($resultadoGestion, 150),
                'resultado' => 'NO CONTACTO',
                'fecha_gestion' => $fechaGestion,
                'observaciones' => null,
                'fuente' => $fuente,
                'metadata' => json_encode($source, JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($data === []) {
            return 0;
        }

        return DB::transaction(function () use ($fuente, $data, $desde, $hasta) {
            Llamada::query()
                ->where('fuente', $fuente)
                ->whereBetween('fecha_gestion', [$desde, $hasta])
                ->delete();

            foreach (array_chunk($data, 500) as $chunk) {
                Llamada::query()->insert($chunk);
            }

            return count($data);
        });
    }

    private function fuenteLlamada(string $tipo): string
    {
        return match ($tipo) {
            'amd' => 'Llamadas_AMD',
            'ivr' => 'Llamadas_IVR',
            'abandonados' => 'Llamadas_Abandonadas',
            default => $tipo,
        };
    }

    private function normalizeCallText(mixed $value, int $limit): string
    {
        return mb_substr(trim((string) $value), 0, $limit);
    }

    private function nombreGestion(string $carteraSlug, array $source): mixed
    {
        return match ($carteraSlug) {
            'apdayc' => $this->firstValue($source, 'socio'),
            'kp-invest' => $this->firstValue($source, 'cliente'),
            default => $this->firstValue($source, 'nombre'),
        };
    }

    private function firstValue(array $row, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return null;
    }

    public function parseMonto(mixed $valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $limpio = str_replace(['S/', 's/', '$', ' '], '', (string) $valor);
        $limpio = str_replace(',', '.', $limpio);

        return is_numeric($limpio) ? (float) $limpio : null;
    }

    public function parseEntero(mixed $valor): ?int
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return is_numeric($valor) ? (int) $valor : null;
    }

    public function parseFecha(mixed $valor): ?string
    {
        if (!$valor || stripos((string) $valor, 'invalida') !== false) {
            return null;
        }

        $timestamp = strtotime(str_replace('/', '-', (string) $valor));

        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }
}
