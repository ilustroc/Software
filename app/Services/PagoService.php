<?php

namespace App\Services;

use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PagoService
{
    public function __construct(private CarteraService $carteras)
    {
    }

    public function procesarCargaMasiva(string $tipo, string $path): int
    {
        $cartera = $this->carteras->findBySlugOrFail($tipo);
        $rows = array_map('str_getcsv', file($path));
        $now = now();
        $insert = [];

        foreach ($rows as $index => $row) {
            $row = array_map(fn ($value) => trim((string) $value), $row);

            if ($index === 0 && strtoupper($row[0] ?? '') === 'DNI') {
                continue;
            }

            if (count($row) < 5 || ($row[0] ?? '') === '') {
                continue;
            }

            $insert[] = [
                'cartera_id' => $cartera->id,
                'dni' => $row[0],
                'operacion' => $row[1] ?? '',
                'moneda' => ($row[2] ?? '') !== '' ? $row[2] : null,
                'fecha' => $this->parseFecha($row[3] ?? null),
                'monto' => $this->parseMonto($row[4] ?? null),
                'gestor' => ($row[5] ?? '') !== '' ? $row[5] : null,
                'origen' => 'csv',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($insert) {
            foreach (array_chunk($insert, 500) as $chunk) {
                Pago::query()->insert($chunk);
            }
        });

        return count($insert);
    }

    private function parseMonto(?string $valor): float
    {
        $limpio = str_replace(['S/', 's/', '$', ' '], '', (string) $valor);
        $limpio = str_replace(',', '.', $limpio);

        return is_numeric($limpio) ? (float) $limpio : 0.00;
    }

    private function parseFecha(?string $valor): string
    {
        if (!$valor) {
            return now()->toDateString();
        }

        return Carbon::parse(str_replace('/', '-', $valor))->toDateString();
    }
}
