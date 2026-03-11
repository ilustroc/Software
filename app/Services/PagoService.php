<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

class PagoService
{
    private function getMetadata(string $tipo)
    {
        return match($tipo) {
            'propia12' => ['tabla' => 'Pagos_1y2', 'sistema' => 1],
            'propia3'  => ['tabla' => 'Pagos_3',   'sistema' => 3],
            'propia4'  => ['tabla' => 'Pagos_4',   'sistema' => 4],
            default    => throw new \Exception("Tipo de pago [$tipo] no soportado."),
        };
    }

    public function procesarCargaMasiva(string $tipo, $path)
    {
        $meta = $this->getMetadata($tipo);
        $rows = array_map('str_getcsv', file($path));
        $insert = [];

        foreach ($rows as $index => $r) {
            // Limpieza básica de la fila
            $r = array_map('trim', $r);

            // Saltar encabezado si existe
            if ($index === 0 && strtoupper($r[0]) === 'DNI') continue;

            // Se espera: DNI(0), OPERACION(1), MONEDA(2), FECHA(3), MONTO(4), GESTOR(5)
            if (count($r) < 5 || empty($r[0])) continue;

            $insert[] = [
                'SISTEMA'   => $meta['sistema'],
                'DNI'       => $r[0],
                'OPERACION' => $r[1],
                'MONEDA'    => $r[2] ?: null,
                'FECHA'     => $this->parseFecha($r[3]),
                'MONTO'     => $this->parseMonto($r[4]),
                'GESTOR'    => $r[5] ?? null,
            ];
        }

        if (empty($insert)) return 0;

        foreach (array_chunk($insert, 500) as $chunk) {
            DB::table($meta['tabla'])->insert($chunk);
        }

        return count($insert);
    }

    private function parseMonto($valor) {
        $limpio = str_replace(['S/', 's/', '$', ' ', ','], ['', '', '', '', ''], $valor);
        // Si el monto viene con coma decimal (ej: 100,50), lo convertimos a punto
        $limpio = str_replace(',', '.', $limpio);
        return is_numeric($limpio) ? (float)$limpio : 0.00;
    }

    private function parseFecha($valor) {
        if (!$valor) return date('Y-m-d');
        $ts = strtotime(str_replace('/', '-', $valor));
        return $ts ? date('Y-m-d', $ts) : date('Y-m-d');
    }
}