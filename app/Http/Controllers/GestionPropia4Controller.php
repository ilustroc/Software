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

        $dataReq = $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ], [], [
            'desde' => 'fecha inicio',
            'hasta' => 'fecha fin',
        ]);

        $desde = $dataReq['desde'];
        $hasta = $dataReq['hasta'];

        $desdeFull = $desde . ' 00:00:00';
        $hastaFull = $hasta . ' 23:59:59';

        try {
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

                // Normaliza nombres de columnas a minúsculas para evitar problemas de case
                $x = array_change_key_case((array) $r, CASE_LOWER);

                $importeFinRaw   = $x['importeCuota'] ?? $x['importe_financiamiento'] ?? null;
                $fechaPromesaRaw = $x['fechapromesa']          ?? $x['fecha_promesa']          ?? null;

                $importeFin   = $this->parseMonto($importeFinRaw);
                $fechaPromesa = $this->parseFecha($fechaPromesaRaw);

                $data[] = [
                    'cliente'                => $x['cliente'] ?? null,
                    'documento'              => $x['documento'] ?? null,
                    'value2'                 => $x['value2'] ?? null,
                    'value1'                 => $x['value1'] ?? null,
                    'fullname'               => $x['fullname'] ?? null,
                    'operacion'              => $x['operacion'] ?? null,
                    'entidad'                => $x['entidad'] ?? null,
                    'dateprocessed'          => $x['dateprocessed'] ?? null,
                    'fechaAgenda'            => $x['fechaagenda'] ?? null,
                    'callerid'               => $x['callerid'] ?? null,
                    'comment'                => $x['comment'] ?? null,

                    'importe_financiamiento' => $importeFin,
                    'nroCuotas'              => $x['nrocuotas'] ?? null,
                    'fecha_promesa'          => $fechaPromesa,

                    'campaign'               => $x['campaign'] ?? null,
                ];
            }

            DB::beginTransaction();

            DB::table('Gestiones_Propia4')
                ->whereBetween('dateprocessed', [$desdeFull, $hastaFull])
                ->delete();

            foreach (array_chunk($data, 500) as $chunk) {
                DB::table('Gestiones_Propia4')->insert($chunk);
            }

            DB::commit();

            return back()->with(
                'msg',
                'Gestiones Propia 4 cargadas correctamente para el rango ' .
                $desde . ' al ' . $hasta .
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
            'A1' => 'cliente',
            'B1' => 'documento',
            'C1' => 'value2',
            'D1' => 'value1',
            'E1' => 'fullname',
            'F1' => 'operacion',
            'G1' => 'entidad',
            'H1' => 'dateprocessed',
            'I1' => 'fechaAgenda',
            'J1' => 'callerid',
            'K1' => 'comment',
            'L1' => 'importe_financiamiento',
            'M1' => 'nroCuotas',
            'N1' => 'fecha_promesa',
            'O1' => 'campaign',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->freezePane('A2');

        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setWidth(20);
        }

        $fileName = 'plantilla_gestiones_sms_p4.xlsx';
        $writer   = new Xlsx($spreadsheet);

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

                $cliente       = trim($row['A'] ?? '');
                $documento     = trim($row['B'] ?? '');
                $value2        = trim($row['C'] ?? '');
                $value1        = trim($row['D'] ?? '');
                $fullname      = trim($row['E'] ?? '');
                $operacion     = trim($row['F'] ?? '');
                $entidad       = trim($row['G'] ?? '');
                $dateprocRaw   = trim($row['H'] ?? '');
                $fechaAgeRaw   = trim($row['I'] ?? '');
                $callerid      = trim($row['J'] ?? '');
                $comment       = trim($row['K'] ?? '');
                $importeRaw    = trim($row['L'] ?? '');
                $nroCuotasRaw  = trim($row['M'] ?? '');
                $fechaPromRaw  = trim($row['N'] ?? '');
                $campaign      = trim($row['O'] ?? '');

                // Fila completamente vacía
                if (
                    $cliente === '' &&
                    $documento === '' &&
                    $value2 === '' &&
                    $value1 === '' &&
                    $fullname === '' &&
                    $operacion === '' &&
                    $entidad === '' &&
                    $dateprocRaw === '' &&
                    $fechaAgeRaw === '' &&
                    $callerid === '' &&
                    $comment === '' &&
                    $importeRaw === '' &&
                    $nroCuotasRaw === '' &&
                    $fechaPromRaw === '' &&
                    $campaign === ''
                ) {
                    continue;
                }

                $dateprocessed = $this->parseFecha($dateprocRaw) ?? date('Y-m-d H:i:s');
                $fechaAgenda   = $this->parseFecha($fechaAgeRaw);
                $fechaPromesa  = $this->parseFecha($fechaPromRaw);
                $importeFin    = $this->parseMonto($importeRaw);
                $nroCuotas     = ($nroCuotasRaw === '' ? null : (int) $nroCuotasRaw);

                $data[] = [
                    'cliente'               => $cliente !== '' ? $cliente : null,
                    'documento'             => $documento !== '' ? $documento : null,
                    'value2'                => $value2 !== '' ? $value2 : null,
                    'value1'                => $value1 !== '' ? $value1 : null,
                    'fullname'              => $fullname !== '' ? $fullname : null,
                    'operacion'             => $operacion !== '' ? $operacion : null,
                    'entidad'               => $entidad !== '' ? $entidad : null,
                    'dateprocessed'         => $dateprocessed,
                    'fechaAgenda'           => $fechaAgenda,
                    'callerid'              => $callerid !== '' ? $callerid : null,
                    'comment'               => $comment !== '' ? $comment : null,
                    'importe_financiamiento'=> $importeFin,
                    'nroCuotas'             => $nroCuotas,
                    'fecha_promesa'         => $fechaPromesa,
                    'campaign'              => $campaign !== '' ? $campaign : null,
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
    private function parseMonto($valor): ?float
    {
        if ($valor === null) return null;

        if (is_numeric($valor)) return (float) $valor;

        $s = trim((string)$valor);
        if ($s === '') return null;

        $s = str_ireplace(['S/.', 'S/', ' '], '', $s);

        if (str_contains($s, ',') && str_contains($s, '.')) {
            $s = str_replace(',', '', $s);
        } else {
            $s = str_replace(',', '.', $s);
        }

        return is_numeric($s) ? (float)$s : null;
    }

    private function parseFecha($valor): ?string
    {
        if ($valor === null) return null;

        if ($valor instanceof \DateTimeInterface) {
            return $valor->format('Y-m-d H:i:s');
        }

        $trim = trim((string)$valor);
        if ($trim === '' || stripos($trim, 'invalida') !== false || stripos($trim, 'inválida') !== false) {
            return null;
        }

        $ts = strtotime($trim);
        return ($ts === false) ? null : date('Y-m-d H:i:s', $ts);
    }
}
