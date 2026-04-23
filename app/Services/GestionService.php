<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

class GestionService
{
    private function getMetadata(string $tipo)
    {
        return match($tipo) {
            'propia12'    => ['sp' => 'spGestionPropia',   'tabla' => 'Gestiones_1y2',    'fecha_col' => 'dateprocessed'],
            'propia3'     => ['sp' => 'spGestionZigor',    'tabla' => 'Gestiones_Propia3', 'fecha_col' => 'dateprocessed'],
            'kpi'         => ['sp' => 'spGestionKpi',      'tabla' => 'Gestiones_Propia4', 'fecha_col' => 'dateprocessed'],
            'apdayc'      => ['sp' => 'spGestionApdayc',    'tabla' => 'Gestiones_APDAYC',  'fecha_col' => 'dateprocessed'],
            'amd'         => ['sp' => 'spLlamadasAMD',     'tabla' => 'Llamadas_AMD',      'fecha_col' => 'calldate'],
            'ivr'         => ['sp' => 'spLlamadasIVR',     'tabla' => 'Llamadas_IVR',      'fecha_col' => 'calldate'],
            'abandonados' => ['sp' => 'spLlamadasAbandonadas', 'tabla' => 'Llamadas_Abandonadas', 'fecha_col' => 'fecha_evento'],
            default       => throw new \Exception("Tipo de gestión [$tipo] no soportado."),
        };
    }

    public function sincronizar(string $tipo, $desde, $hasta)
    {
        $meta = $this->getMetadata($tipo);

        $desdeFull = (strpos($desde, ':') !== false) ? $desde : $desde . ' 00:00:00';
        $hastaFull = (strpos($hasta, ':') !== false) ? $hasta : $hasta . ' 23:59:59';

        $rows = DB::connection('crm')->select("CALL {$meta['sp']}(?, ?)", [$desdeFull, $hastaFull]);
        if (empty($rows)) return 0;

        // AMD: excluir campañas IVR_*
        if ($tipo === 'amd') {
            $rows = array_filter($rows, function ($r) {
                $row = array_change_key_case((array)$r, CASE_LOWER);
                $campaign = strtoupper(trim((string)($row['campaign'] ?? '')));
                return !str_starts_with($campaign, 'IVR_');
            });

            $rows = array_values($rows);
        }

        if (empty($rows)) return 0;

        $data = array_map(function($r) use ($tipo) {
            $row = array_change_key_case((array)$r, CASE_LOWER);
            
            if ($tipo === 'amd' || $tipo === 'ivr') {
                return [
                    'calldate'    => $row['calldate'] ?? null,
                    'campaign'    => $row['campaign'] ?? null,
                    'dst'         => $row['dst'] ?? null,
                    'disposition' => $row['disposition'] ?? null,
                    'userfield'   => $row['userfield'] ?? null,
                    'contact'     => $row['contact'] ?? null,
                    'dialbase'    => $row['dialbase'] ?? null,
                    'doc'         => $row['doc'] ?? null,
                ];
            }

            if ($tipo === 'abandonados') {
                return [
                    'fecha_evento' => $row['datetime'] ?? null,
                    'event'        => $row['event'] ?? null,
                    'callidnum'    => $row['callidnum'] ?? null,
                    'guid'         => $row['guid'] ?? null,
                    'queue'        => $row['queue'] ?? null,
                    'enterdate'    => $row['enterdate'] ?? null,
                    'posabandon'   => $row['posabandon'] ?? null,
                    'posoriginal'  => $row['posoriginal'] ?? null,
                    'callerid'     => $row['callerid'] ?? null,
                    'timewait'     => $row['timewait'] ?? null,
                    'documento'    => $row['documento'] ?? null,
                ];
            }

            $montoRaw = $row['pagar_por_cuota'] ?? $row['importecuota'] ?? $row['importe_financiamiento'] ?? $row['importefinanciamiento'] ?? null;
            
            $item = [
                'documento'     => $row['documento'] ?? null,
                'value2'        => $row['value2'] ?? null,
                'value1'        => $row['value1'] ?? null,
                'fullname'      => $row['fullname'] ?? null,
                'operacion'     => $row['operacion'] ?? null,
                'dateprocessed' => $row['dateprocessed'] ?? null,
                'fechaAgenda'   => $row['fechaagenda'] ?? null,
                'callerid'      => $row['callerid'] ?? null,
                'comment'       => $row['comment'] ?? null,
                'nroCuotas'     => $row['nrocuotas'] ?? null,
                'campaign'      => $row['campaign'] ?? null,
                'fecha_promesa' => $this->parseFecha($row['fecha_promesa'] ?? $row['fechapromesa'] ?? null),
            ];

            if ($tipo === 'apdayc') {
                return [
                    'documento'     => $row['documento'] ?? null,
                    'LIC_ID'        => $row['lic_id'] ?? null,
                    'socio'         => $row['socio'] ?? null,
                    'value2'        => $row['value2'] ?? null,
                    'value1'        => $row['value1'] ?? null,
                    'fullname'      => $row['fullname'] ?? null,
                    'dateprocessed' => $row['dateprocessed'] ?? null,
                    'fechaAgenda'   => $row['fechaagenda'] ?? null,
                    'callerid'      => $row['callerid'] ?? null,
                    'comment'       => $row['comment'] ?? null,
                    'montoPromesa'  => $this->parseMonto($row['montopromesa'] ?? null),
                    'nroCuota'      => $row['nrocuota'] ?? null,
                    'fecha_promesa' => $this->parseFecha($row['fecha_promesa'] ?? null),
                    'campaign'      => $row['campaign'] ?? null,
                    'dateprocessed' => $row['dateprocessed'] ?? null,
                ];
            }
            
            if ($tipo === 'kpi') {
                $item['cliente'] = $row['cliente'] ?? $row['nombre'] ?? null;
                $item['entidad'] = $row['entidad'] ?? null;
                $item['importe_financiamiento'] = $this->parseMonto($montoRaw);

            } elseif ($tipo === 'propia3') {
                $item['nombre'] = $row['nombre'] ?? $row['cliente'] ?? null;
                $item['ctl'] = $row['ctl'] ?? null;
                $item['pagar_por_cuota'] = $this->parseMonto($montoRaw);

            } elseif ($tipo === 'propia12') {
                $item['nombre'] = $row['nombre'] ?? $row['cliente'] ?? null;
                $item['entidad'] = $row['entidad'] ?? null;
                $item['cartera'] = $row['cartera'] ?? null;
                $item['pagar_por_cuota'] = $this->parseMonto($montoRaw);
            }

            return $item;
        }, $rows);

        return DB::transaction(function () use ($data, $meta, $desdeFull, $hastaFull) {
            DB::table($meta['tabla'])->whereBetween($meta['fecha_col'], [$desdeFull, $hastaFull])->delete();
            
            foreach (array_chunk($data, 500) as $chunk) {
                DB::table($meta['tabla'])->insert($chunk);
            }
            return count($data);
        });
    }

    public function parseMonto($valor) {
        if (!$valor) return 0.00;
        $limpio = str_replace(['S/', 's/', ' ', ','], ['', '', '', '.'], $valor);
        return is_numeric($limpio) ? (float)$limpio : 0.00;
    }

    public function parseFecha($valor) {
        if (!$valor || stripos($valor, 'invalida') !== false) return null;
        $ts = strtotime($valor);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }
}