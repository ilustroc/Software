<?php

namespace App\Http\Controllers;

use App\Models\Cartera;
use App\Services\CarteraService;
use App\Services\GestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class GestionController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $carteras = Cartera::query()
            ->activa()
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $carteraId = $request->integer('cartera_id') ?: $carteras->first()?->id;
        $cartera = $carteraId ? $carteras->firstWhere('id', $carteraId) : null;
        $tieneSincronizacion = $cartera && $this->getConfig($cartera->slug) !== null;

        return view('gestiones.index', compact('carteras', 'carteraId', 'cartera', 'tieneSincronizacion'));
    }

    public function legacy(string $tipo, CarteraService $carteras)
    {
        try {
            $cartera = $carteras->findBySlugOrFail($tipo);

            return redirect()->route('gestiones.index', ['cartera_id' => $cartera->id]);
        } catch (Throwable) {
            return redirect()->route('gestiones.index');
        }
    }

    public function indexAmd(Request $request)
    {
        return $this->indexLlamadas($request, 'amd', 'gestiones.amd');
    }

    public function indexIvr(Request $request)
    {
        return $this->indexLlamadas($request, 'ivr', 'gestiones.ivr');
    }

    public function indexAbandonados(Request $request)
    {
        return $this->indexLlamadas($request, 'abandonados', 'gestiones.abandonados');
    }

    public function cargar(Request $request, GestionService $service)
    {
        $data = $request->validate([
            'cartera_id' => ['required', 'integer', 'exists:carteras,id'],
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ]);

        $cartera = Cartera::query()->findOrFail($data['cartera_id']);

        if ($this->getConfig($cartera->slug) === null) {
            return back()->withInput()->with(
                'error',
                "La cartera {$cartera->nombre} no tiene sincronizacion CRM configurada. Usa la carga manual XLSX.",
            );
        }

        try {
            $count = $service->sincronizar($cartera->slug, $data['desde'], $data['hasta']);

            return back()->with('msg', "Sincronizacion de {$cartera->nombre} exitosa: {$count} registros procesados.");
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Error en la sincronizacion: ' . $e->getMessage());
        }
    }

    private function indexLlamadas(Request $request, string $tipo, string $view)
    {
        $desde = $request->desde ?? date('Y-m-d');
        $hasta = $request->hasta ?? date('Y-m-d');
        $fuente = match ($tipo) {
            'amd' => 'Llamadas_AMD',
            'ivr' => 'Llamadas_IVR',
            'abandonados' => 'Llamadas_Abandonadas',
            default => $tipo,
        };

        $query = DB::table('llamadas')
            ->where('fuente', $fuente)
            ->whereBetween('fecha_gestion', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->orderByDesc('fecha_gestion');

        if ($tipo === 'abandonados') {
            $query->select(
                'fecha_gestion as fecha_evento',
                'resultado_gestion as event',
                DB::raw('NULL as queue'),
                'telefono as callerid',
                DB::raw('NULL as timewait'),
                'dni as documento',
            );
        } else {
            $query->select(
                'fecha_gestion as calldate',
                DB::raw('NULL as campaign'),
                'telefono as dst',
                'resultado_gestion as disposition',
                DB::raw('NULL as userfield'),
                DB::raw('NULL as dialbase'),
                'dni as doc',
            );
        }

        $registros = $query->paginate(10)->appends($request->query());

        return view($view, compact('registros', 'desde', 'hasta'));
    }

    private function getConfig(string $tipo): ?array
    {
        return match ($tipo) {
            'propia12' => [
                'cartera' => 'propia12',
                'file_name' => 'plantilla_p12.xlsx',
                'headers' => ['documento', 'nombre', 'value2', 'value1', 'fullname', 'operacion', 'entidad', 'cartera', 'dateprocessed', 'fechaAgenda', 'callerid', 'comment', 'pagar_por_cuota', 'nroCuotas', 'fecha_promesa', 'campaign'],
            ],
            'propia3' => [
                'cartera' => 'propia3',
                'file_name' => 'plantilla_p3.xlsx',
                'headers' => ['documento', 'nombre', 'value2', 'value1', 'fullname', 'operacion', 'ctl', 'dateprocessed', 'fechaAgenda', 'callerid', 'comment', 'pagar_por_cuota', 'nroCuotas', 'fecha_promesa', 'campaign'],
            ],
            'kpi', 'kp-invest', 'propia4' => [
                'cartera' => 'kp-invest',
                'file_name' => 'plantilla_kp_invest.xlsx',
                'headers' => ['documento', 'cliente', 'value2', 'value1', 'fullname', 'operacion', 'entidad', 'dateprocessed', 'fechaAgenda', 'callerid', 'comment', 'importe_financiamiento', 'nroCuotas', 'fecha_promesa', 'campaign'],
            ],
            'apdayc' => [
                'cartera' => 'apdayc',
                'file_name' => 'plantilla_apdayc.xlsx',
                'headers' => ['documento', 'LIC_ID', 'socio', 'value2', 'value1', 'fullname', 'fechaAgenda', 'dateprocessed', 'callerid', 'comment', 'montoPromesa', 'nroCuota', 'fecha_promesa', 'campaign'],
            ],
            default => null,
        };
    }

    private function genericConfig(Cartera $cartera): array
    {
        return [
            'cartera' => $cartera->slug,
            'file_name' => 'plantilla_gestiones_' . $cartera->slug . '.xlsx',
            'headers' => [
                'documento',
                'cliente',
                'tipificacion',
                'resultado',
                'asesor',
                'operacion',
                'entidad',
                'subcartera',
                'fecha_gestion',
                'fecha_agenda',
                'telefono',
                'comentario',
                'monto_promesa',
                'nro_cuotas',
                'fecha_promesa',
                'campaign',
            ],
        ];
    }

    public function plantillaManual(Request $request)
    {
        $data = $request->validate([
            'cartera_id' => ['required', 'integer', 'exists:carteras,id'],
        ]);

        $cartera = Cartera::query()->findOrFail($data['cartera_id']);
        $config = $this->getConfig($cartera->slug) ?? $this->genericConfig($cartera);
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

    public function cargarManual(Request $request)
    {
        $data = $request->validate([
            'cartera_id' => ['required', 'integer', 'exists:carteras,id'],
            'archivo' => ['required', 'file', 'mimes:xlsx', 'max:15360'],
        ]);

        $cartera = Cartera::query()->findOrFail($data['cartera_id']);
        $config = $this->getConfig($cartera->slug) ?? $this->genericConfig($cartera);

        try {
            $spreadsheet = IOFactory::load($request->file('archivo')->getRealPath());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            $headers = $config['headers'];
            $data = [];
            $now = now();

            foreach ($rows as $index => $row) {
                if ($index === 1 || empty(trim((string) ($row['A'] ?? '')))) {
                    continue;
                }

                $rowData = [];
                foreach ($headers as $idx => $key) {
                    $columnLetter = Coordinate::stringFromColumnIndex($idx + 1);
                    $rowData[$key] = $this->normalizeValue($key, $row[$columnLetter] ?? null);
                }

                $data[] = $this->mapGestionManual((int) $cartera->id, $rowData, $now);
            }

            if (empty($data)) {
                return back()->withInput()->with('error', 'No hay datos validos en el XLSX.');
            }

            DB::transaction(function () use ($data) {
                foreach (array_chunk($data, 500) as $chunk) {
                    DB::table('gestiones')->insert($chunk);
                }
            });

            return back()->with('msg', 'Carga manual exitosa: ' . count($data) . " registros en {$cartera->nombre}.");
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Error al cargar gestiones: ' . $e->getMessage());
        }
    }

    private function normalizeValue(string $key, mixed $value): mixed
    {
        $value = $value instanceof \DateTimeInterface ? $value : trim((string) $value);

        if (in_array($key, ['dateprocessed', 'fechaAgenda', 'fecha_agenda', 'fecha_gestion', 'fecha_promesa'], true)) {
            return $this->parseExcelDate($value);
        }

        if (in_array($key, ['pagar_por_cuota', 'importe_financiamiento', 'montoPromesa', 'monto_promesa'], true)) {
            return ($value === '' || $value === null) ? null : (float) str_replace(['$', ',', ' '], '', (string) $value);
        }

        if (in_array($key, ['nroCuotas', 'nroCuota', 'nro_cuotas'], true)) {
            return ($value === '' || $value === null) ? null : (int) $value;
        }

        return $value === '' ? null : $value;
    }

    private function mapGestionManual(int $carteraId, array $row, \DateTimeInterface $now): array
    {
        return [
            'cartera_id' => $carteraId,
            'documento' => $row['documento'] ?? null,
            'licencia_id' => $row['LIC_ID'] ?? null,
            'socio' => $row['socio'] ?? null,
            'cliente' => $row['nombre'] ?? $row['cliente'] ?? $row['socio'] ?? null,
            'tipificacion' => $row['value2'] ?? $row['tipificacion'] ?? null,
            'resultado' => $row['value1'] ?? $row['resultado'] ?? null,
            'asesor' => $row['fullname'] ?? $row['asesor'] ?? null,
            'operacion' => $row['operacion'] ?? null,
            'entidad' => $row['entidad'] ?? null,
            'subcartera' => $row['cartera'] ?? $row['ctl'] ?? $row['subcartera'] ?? null,
            'fecha_gestion' => $row['dateprocessed'] ?? $row['fecha_gestion'] ?? null,
            'fecha_agenda' => $row['fechaAgenda'] ?? $row['fecha_agenda'] ?? null,
            'telefono' => $row['callerid'] ?? $row['telefono'] ?? null,
            'comentario' => $row['comment'] ?? $row['comentario'] ?? null,
            'monto_promesa' => $row['pagar_por_cuota'] ?? $row['importe_financiamiento'] ?? $row['montoPromesa'] ?? $row['monto_promesa'] ?? null,
            'nro_cuotas' => $row['nroCuotas'] ?? $row['nroCuota'] ?? $row['nro_cuotas'] ?? null,
            'fecha_promesa' => $row['fecha_promesa'] ?? null,
            'campaign' => $row['campaign'] ?? null,
            'origen' => 'manual',
            'metadata' => json_encode($row, JSON_UNESCAPED_UNICODE),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function parseExcelDate(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        if ($valor instanceof \DateTimeInterface) {
            return $valor->format('Y-m-d H:i:s');
        }

        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        if (is_numeric($valor)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $valor)->format('Y-m-d H:i:s');
            } catch (Throwable) {
                return null;
            }
        }

        $formatos = [
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'd/m/Y',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
            'm/d/Y H:i:s',
            'm/d/Y H:i',
            'm/d/Y',
        ];

        foreach ($formatos as $formato) {
            $dt = \DateTime::createFromFormat($formato, $valor);

            if ($dt !== false) {
                return $dt->format('Y-m-d H:i:s');
            }
        }

        $timestamp = strtotime(str_replace('/', '-', $valor));

        return $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : null;
    }
}
