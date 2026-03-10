<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Jobs\ProcesarSincronizacionJob;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class GestionController extends Controller
{
    /**
     * VISTAS DE LOS FORMULARIOS Y TABLAS
     */
    public function formPropia12() { return view('gestiones.propia12'); }
    public function formPropia3()  { return view('gestiones.propia3'); }
    public function formKpi()      { return view('gestiones.kpi'); }

    public function indexAmd(Request $request)
    {
        $desde = $request->desde ?? date('Y-m-d');
        $hasta = $request->hasta ?? date('Y-m-d');
        $registros = DB::table('Llamadas_AMD')
            ->whereBetween('calldate', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->orderBy('calldate', 'desc')
            ->paginate(10)->appends($request->query());

        return view('gestiones.amd', compact('registros', 'desde', 'hasta'));
    }

    public function indexAbandonados(Request $request)
    {
        $desde = $request->desde ?? date('Y-m-d');
        $hasta = $request->hasta ?? date('Y-m-d');
        $registros = DB::table('Llamadas_Abandonadas')
            ->whereBetween('fecha_evento', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->orderBy('fecha_evento', 'desc')
            ->paginate(10)->appends($request->query());

        return view('gestiones.abandonados', compact('registros', 'desde', 'hasta'));
    }

    /**
     * SINCRONIZACIÓN DESDE CRM (Usando Jobs para segundo plano)
     */
    public function cargar(Request $request, $tipo)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        try {
            // Enviamos el trabajo a la cola
            ProcesarSincronizacionJob::dispatch(
                $tipo, 
                $request->desde, 
                $request->hasta
            );

            // Respuesta inmediata al usuario
            return back()->with('msg', "Se ha iniciado la sincronización de $tipo en segundo plano. Los datos aparecerán en breve.");
            
        } catch (Throwable $e) {
            return back()->with('error', 'Error al programar la carga: ' . $e->getMessage());
        }
    }

    /**
     * CONFIGURACIÓN PARA CARGA MANUAL (EXCEL)
     */
    private function getConfig($tipo)
    {
        return match ($tipo) {
            'propia12' => [
                'tabla' => 'Gestiones_1y2',
                'file_name' => 'plantilla_p12.xlsx',
                'headers' => ['documento', 'nombre', 'value2', 'value1', 'fullname', 'operacion', 'entidad', 'cartera', 'dateprocessed', 'fechaAgenda', 'callerid', 'comment', 'pagar_por_cuota', 'nroCuotas', 'fecha_promesa', 'campaign']
            ],
            'propia3' => [
                'tabla' => 'Gestiones_Propia3',
                'file_name' => 'plantilla_p3.xlsx',
                'headers' => ['documento', 'nombre', 'value2', 'value1', 'fullname', 'operacion', 'ctl', 'dateprocessed', 'fechaAgenda', 'callerid', 'comment', 'pagar_por_cuota', 'nroCuotas', 'fecha_promesa', 'campaign']
            ],
            'kpi' => [
                'tabla' => 'Gestiones_Propia4',
                'file_name' => 'plantilla_kpi.xlsx',
                'headers' => ['documento', 'cliente', 'value2', 'value1', 'fullname', 'operacion', 'entidad', 'dateprocessed', 'fechaAgenda', 'callerid', 'comment', 'importe_financiamiento', 'nroCuotas', 'fecha_promesa', 'campaign']
            ],
            'amd' => [
                'tabla' => 'Llamadas_AMD',
                'file_name' => 'plantilla_amd.xlsx',
                'headers' => ['calldate', 'campaign', 'dst', 'disposition', 'userfield', 'contact', 'dialbase', 'doc']
            ],
            'abandonados' => [
                'tabla' => 'Llamadas_Abandonadas',
                'file_name' => 'plantilla_abandonados.xlsx',
                'headers' => ['fecha_evento', 'event', 'callidnum', 'guid', 'queue', 'enterdate', 'posabandon', 'posoriginal', 'callerid', 'timewait', 'documento']
            ],
            default => null,
        };
    }

    /**
     * DESCARGA DE PLANTILLA EXCEL
     */
    public function plantillaManual($tipo)
    {
        $config = $this->getConfig($tipo);
        if (!$config) return back()->with('error', 'Cartera no válida.');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        foreach ($config['headers'] as $index => $label) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
            $sheet->getColumnDimensionByColumn($index + 1)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $config['file_name']);
    }

    /**
     * CARGA MANUAL EXCEL
     */
    public function cargarManual(Request $request, $tipo)
    {
        $config = $this->getConfig($tipo);
        if (!$config) return back()->with('error', 'Configuración no encontrada.');

        $request->validate(['archivo' => 'required|file|mimes:xlsx|max:15360']);

        try {
            $spreadsheet = IOFactory::load($request->file('archivo')->getRealPath());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            $headers = $config['headers'];
            $data = [];

            foreach ($rows as $index => $row) {
                if ($index === 1 || empty(trim($row['A'] ?? ''))) continue;

                $rowData = [];
                foreach ($headers as $idx => $key) {
                    $columnLetter = Coordinate::stringFromColumnIndex($idx + 1);
                    $val = trim($row[$columnLetter] ?? '');

                    if (in_array($key, ['dateprocessed', 'fechaAgenda', 'fecha_promesa', 'calldate', 'fecha_evento', 'enterdate'])) {
                        $rowData[$key] = $this->parseExcelDate($val);
                    } elseif (in_array($key, ['pagar_por_cuota', 'importe_financiamiento'])) {
                        $rowData[$key] = (float) str_replace(['$', ',', ' '], '', $val);
                    } else {
                        $rowData[$key] = ($val === '') ? null : $val;
                    }
                }
                $data[] = $rowData;
            }

            if (empty($data)) return back()->with('error', 'No hay datos válidos.');

            DB::beginTransaction();
            foreach (array_chunk($data, 500) as $chunk) {
                DB::table($config['tabla'])->insert($chunk);
            }
            DB::commit();

            return back()->with('msg', "Carga manual exitosa: " . count($data) . " registros en $tipo.");

        } catch (Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function parseExcelDate($valor): ?string
    {
        if (empty($valor)) return null;
        if (is_numeric($valor)) return ExcelDate::excelToDateTimeObject($valor)->format('Y-m-d H:i:s');
        $ts = strtotime(str_replace('/', '-', $valor));
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }
}