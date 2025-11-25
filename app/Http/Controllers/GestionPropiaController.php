<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

// Para leer Excel
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class GestionPropiaController extends Controller
{
    public function form()
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        return view('gestiones.propia12');
    }

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

        // Crear fechas con hora completa
        $desdeFull = $desde . ' 00:00:00';
        $hastaFull = $hasta . ' 23:59:59';

        try {
            // 2) Ejecutar el SP con rango de fechas en el CRM
            $rows = DB::connection('crm')->select(
                'CALL spGestionPropia(?, ?)',
                [$desdeFull, $hastaFull]
            );

            if (empty($rows)) {
                return back()->with('msg', "El SP no devolvió registros para el rango $desde al $hasta.");
            }

            // 3) Preparar datos a insertar
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
                    'entidad'         => $r->entidad ?? null,
                    'cartera'         => $r->cartera ?? null,
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

            // 4) BORRAR SOLO EL TRAMO en nuestra tabla
            DB::table('Gestiones_1y2')
                ->whereBetween('dateprocessed', [$desdeFull, $hastaFull])
                ->delete();

            // 5) Insertar nuevos registros
            foreach (array_chunk($data, 500) as $chunk) {
                DB::table('Gestiones_1y2')->insert($chunk);
            }

            DB::commit();

            return back()->with('msg',
                'Gestiones cargadas correctamente para el rango ' . $desde . ' al ' . $hasta .
                '. Total registros: ' . count($data)
            );

        } catch (Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cargar gestiones: ' . $e->getMessage());
        }
    }

    /**
     * NUEVO: importar gestiones desde un Excel con la plantilla.
     */
    public function importExcel(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'], // 10MB
        ], [], [
            'archivo' => 'archivo Excel',
        ]);

        $file = $request->file('archivo');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true);

            if (count($rows) <= 1) {
                return back()->with('error', 'El archivo no contiene datos.');
            }

            // Fila 1 = encabezados
            $headerRow = array_shift($rows);

            // Mapear encabezados a índice de columna
            $map = $this->mapHeaders($headerRow, [
                'documento',
                'nombre',
                'value2',
                'value1',
                'fullname',
                'operacion',
                'entidad',
                'cartera',
                'dateprocessed',
                'fechaAgenda',
                'callerid',
                'comment',
                'pagar_por_cuota',
                'nroCuotas',
                'fecha_promesa',
                'campaign',
            ]);

            $data = [];
            foreach ($rows as $row) {
                // Si no hay documento ni dateprocessed, consideramos fila vacía
                $documento = $this->getCell($row, $map, 'documento');
                $dateProc  = $this->getCell($row, $map, 'dateprocessed');

                if ($documento === '' && $dateProc === '') {
                    continue;
                }

                $montoCuota   = $this->parseMonto($this->getCell($row, $map, 'pagar_por_cuota'));
                $fechaPromesa = $this->parseExcelDate($this->getCell($row, $map, 'fecha_promesa'));
                $fechaAgenda  = $this->parseExcelDate($this->getCell($row, $map, 'fechaAgenda'));
                $dateProcessed= $this->parseExcelDate($dateProc);

                $data[] = [
                    'documento'       => $documento ?: null,
                    'nombre'          => $this->getCell($row, $map, 'nombre') ?: null,
                    'value2'          => $this->getCell($row, $map, 'value2') ?: null,
                    'value1'          => $this->getCell($row, $map, 'value1') ?: null,
                    'fullname'        => $this->getCell($row, $map, 'fullname') ?: null,
                    'operacion'       => $this->getCell($row, $map, 'operacion') ?: null,
                    'entidad'         => $this->getCell($row, $map, 'entidad') ?: null,
                    'cartera'         => $this->getCell($row, $map, 'cartera') ?: null,
                    'dateprocessed'   => $dateProcessed,
                    'fechaAgenda'     => $fechaAgenda,
                    'callerid'        => $this->getCell($row, $map, 'callerid') ?: null,
                    'comment'         => $this->getCell($row, $map, 'comment') ?: null,
                    'pagar_por_cuota' => $montoCuota,
                    'nroCuotas'       => $this->getCell($row, $map, 'nroCuotas') !== '' ? (int)$this->getCell($row, $map, 'nroCuotas') : null,
                    'fecha_promesa'   => $fechaPromesa,
                    'campaign'        => $this->getCell($row, $map, 'campaign') ?: null,
                ];
            }

            if (empty($data)) {
                return back()->with('error', 'No se encontraron filas válidas en el Excel.');
            }

            DB::beginTransaction();

            // Insertar en bloques
            foreach (array_chunk($data, 500) as $chunk) {
                DB::table('Gestiones_1y2')->insert($chunk);
            }

            DB::commit();

            return back()->with('msg', 'Gestiones importadas correctamente desde Excel. Total registros: ' . count($data));

        } catch (Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al importar gestiones desde Excel: ' . $e->getMessage());
        }
    }

    /**
     * Convierte un string a decimal (pagar_por_cuota).
     */
    private function parseMonto(?string $valor): ?float
    {
        if ($valor === null || trim($valor) === '') {
            return null;
        }

        // Eliminar símbolos extra, comas, espacios
        $limpio = str_replace(['S/', 's/', ' ', ','], ['', '', '', '.'], $valor);

        if (!is_numeric($limpio)) {
            return null;
        }

        return (float) $limpio;
    }

    /**
     * Convierte un string a DATETIME válido o null.
     * Ignora cosas como "Fecha inválida".
     */
    private function parseFecha(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $trim = trim($valor);
        if ($trim === '' || stripos($trim, 'inválida') !== false || stripos($trim, 'invalida') !== false) {
            return null;
        }

        $ts = strtotime($trim);
        if ($ts === false) {
            return null;
        }

        // Formato MySQL DATETIME
        return date('Y-m-d H:i:s', $ts);
    }

    /**
     * Convierte una celda de Excel (string o número) en DATETIME MySQL.
     */
    private function parseExcelDate($valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        // Si es numérico, asumimos serial de Excel
        if (is_numeric($valor)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject($valor);
                return $dt->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Si es texto, reutilizamos parseFecha
        return $this->parseFecha((string)$valor);
    }

    /**
     * Mapea encabezados a claves.
     */
    private function mapHeaders(array $headerRow, array $expected): array
    {
        $map = [];

        // $headerRow viene como ['A' => 'documento', 'B' => 'nombre', ...]
        foreach ($headerRow as $col => $name) {
            $nameLower = strtolower(trim((string)$name));
            foreach ($expected as $exp) {
                if ($nameLower === strtolower($exp)) {
                    $map[$exp] = $col;
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * Obtiene el valor de una celda usando el mapa de headers.
     */
    private function getCell(array $row, array $map, string $key): string
    {
        if (!isset($map[$key])) {
            return '';
        }
        $col = $map[$key];
        return isset($row[$col]) ? trim((string)$row[$col]) : '';
    }
}
