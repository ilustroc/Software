<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GestionPropia4Controller extends Controller
{
    public function form()
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        return view('gestiones.propia4');
    }

    /**
     * Cargar gestiones Propia 4 desde SP spGestionKpi
     */
    public function cargar(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        // Validar fechas
        $dataReq = $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ], [], [
            'desde' => 'fecha inicio',
            'hasta' => 'fecha fin',
        ]);

        $desde = $dataReq['desde'];
        $hasta = $dataReq['hasta'];

        // Rango con hora completa
        $desdeFull = $desde . ' 00:00:00';
        $hastaFull = $hasta . ' 23:59:59';

        try {
            // Ejecutar SP de KPI en el CRM
            $rows = DB::connection('crm')->select(
                'CALL spGestionKpi(?, ?)',
                [$desdeFull, $hastaFull]
            );

            if (empty($rows)) {
                return back()->with(
                    'msg',
                    "El SP no devolvió registros para el rango $desde al $hasta."
                );
            }

            $data = [];

            foreach ($rows as $r) {
                $montoCuota   = $this->parseMonto($r->pagar_por_cuota ?? null);
                $fechaPromesa = $this->parseFecha($r->fecha_promesa ?? null);

                $data[] = [
                    'documento'       => $r->documento ?? null,
                    'nombre'          => $r->nombre ?? null,
                    'value2'          => $r->value2 ?? null,
                    'value1'          => $r->value1 ?? null,
                    'fullname'        => $r->fullname ?? null,
                    'operacion'       => $r->operacion ?? null,
                    'ctl'             => $r->ctl ?? null,
                    'dateprocessed'   => $r->dateprocessed ?? null,
                    'fechaAgenda'     => $r->fechaAgenda ?? null,
                    'callerid'        => $r->callerid ?? null,
                    'comment'         => $r->comment ?? null,
                    'pagar_por_cuota' => $montoCuota,
                    'nroCuotas'       => $r->nroCuotas ?? null,
                    'fecha_promesa'   => $fechaPromesa,
                    'campaign'        => $r->campaign ?? null,
                ];
            }

            DB::beginTransaction();

            // Borrar el tramo en Gestiones_Propia4
            DB::table('Gestiones_Propia4')
                ->whereBetween('dateprocessed', [$desdeFull, $hastaFull])
                ->delete();

            foreach (array_chunk($data, 500) as $chunk) {
                DB::table('Gestiones_Propia4')->insert($chunk);
            }

            DB::commit();

            return back()->with(
                'msg',
                'Gestiones Propia 4 cargadas correctamente para el rango '
                . $desde . ' al ' . $hasta .
                '. Total registros: ' . count($data)
            );

        } catch (Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cargar gestiones Propia 4: ' . $e->getMessage());
        }
    }

    /**
     * DESCARGAR PLANTILLA XLSX PARA SMS
     */
    public function plantillaSms()
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Gestiones_SMS_P4');

        $headers = [
            'A1' => 'documento',
            'B1' => 'nombre',
            'C1' => 'value2',
            'D1' => 'value1',
            'E1' => 'fullname',
            'F1' => 'operacion',
            'G1' => 'ctl',
            'H1' => 'dateprocessed',
            'I1' => 'fechaAgenda',
            'J1' => 'callerid',
            'K1' => 'comment',
            'L1' => 'pagar_por_cuota',
            'M1' => 'nroCuotas',
            'N1' => 'fecha_promesa',
            'O1' => 'campaign',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->freezePane('A2');

        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setWidth(18);
        }

        $fileName = 'plantilla_gestiones_sms_p4.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * CARGA DE GESTIONES SMS DESDE XLSX
     */
    public function cargarSms(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx'],
        ], [], [
            'archivo' => 'archivo XLSX',
        ]);

        try {
            $file = $request->file('archivo');
            $path = $file->getRealPath();

            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows  = $sheet->toArray(null, true, true, true);

            $data = [];
            $insertados = 0;

            foreach ($rows as $index => $row) {
                if ($index === 1) {
                    // encabezados
                    continue;
                }

                $documento       = trim($row['A'] ?? '');
                $nombre          = trim($row['B'] ?? '');
                $value2          = trim($row['C'] ?? '');
                $value1          = trim($row['D'] ?? '');
                $fullname        = trim($row['E'] ?? '');
                $operacion       = trim($row['F'] ?? '');
                $ctl             = trim($row['G'] ?? '');
                $dateprocessedRaw= trim($row['H'] ?? '');
                $fechaAgendaRaw  = trim($row['I'] ?? '');
                $callerid        = trim($row['J'] ?? '');
                $comment         = trim($row['K'] ?? '');
                $pagarRaw        = trim($row['L'] ?? '');
                $nroCuotasRaw    = trim($row['M'] ?? '');
                $fechaPromRaw    = trim($row['N'] ?? '');
                $campaign        = trim($row['O'] ?? '');

                // Fila completamente vacía
                if (
                    $documento === '' &&
                    $nombre === '' &&
                    $value2 === '' &&
                    $value1 === '' &&
                    $fullname === '' &&
                    $operacion === '' &&
                    $ctl === '' &&
                    $dateprocessedRaw === '' &&
                    $fechaAgendaRaw === '' &&
                    $callerid === '' &&
                    $comment === '' &&
                    $pagarRaw === '' &&
                    $nroCuotasRaw === '' &&
                    $fechaPromRaw === '' &&
                    $campaign === ''
                ) {
                    continue;
                }

                $dateprocessed = $this->parseFecha($dateprocessedRaw) ?? date('Y-m-d H:i:s');
                $fechaAgenda   = $this->parseFecha($fechaAgendaRaw);
                $fechaPromesa  = $this->parseFecha($fechaPromRaw);
                $pagarCuota    = $this->parseMonto($pagarRaw);
                $nroCuotas     = ($nroCuotasRaw === '' ? null : (int) $nroCuotasRaw);

                $data[] = [
                    'documento'       => $documento !== '' ? $documento : null,
                    'nombre'          => $nombre !== '' ? $nombre : null,
                    'value2'          => $value2 !== '' ? $value2 : null,
                    'value1'          => $value1 !== '' ? $value1 : null,
                    'fullname'        => $fullname !== '' ? $fullname : null,
                    'operacion'       => $operacion !== '' ? $operacion : null,
                    'ctl'             => $ctl !== '' ? $ctl : null,
                    'dateprocessed'   => $dateprocessed,
                    'fechaAgenda'     => $fechaAgenda,
                    'callerid'        => $callerid !== '' ? $callerid : null,
                    'comment'         => $comment !== '' ? $comment : null,
                    'pagar_por_cuota' => $pagarCuota,
                    'nroCuotas'       => $nroCuotas,
                    'fecha_promesa'   => $fechaPromesa,
                    'campaign'        => $campaign !== '' ? $campaign : null,
                ];
            }

            if (empty($data)) {
                return back()->with('error', 'El archivo no contiene filas válidas de gestiones SMS.');
            }

            foreach (array_chunk($data, 500) as $chunk) {
                DB::table('Gestiones_Propia4')->insert($chunk);
                $insertados += count($chunk);
            }

            return back()->with(
                'msg',
                "Gestiones SMS Propia 4 cargadas correctamente. Total insertadas: $insertados"
            );

        } catch (Throwable $e) {
            return back()->with('error', 'Error al cargar gestiones SMS: ' . $e->getMessage());
        }
    }

    // Helpers ============================

    private function parseMonto(?string $valor): ?float
    {
        if ($valor === null || trim($valor) === '') {
            return null;
        }

        $limpio = str_replace(['S/', 's/', ' ', ','], ['', '', '', '.'], $valor);

        if (!is_numeric($limpio)) {
            return null;
        }

        return (float) $limpio;
    }

    private function parseFecha(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $trim = trim($valor);
        if ($trim === '' ||
            stripos($trim, 'inválida') !== false ||
            stripos($trim, 'invalida') !== false) {
            return null;
        }

        $ts = strtotime($trim);
        if ($ts === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $ts);
    }
}
