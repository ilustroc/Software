<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportPagosRequest;
use App\Http\Requests\StorePagoRequest;
use App\Models\Cartera;
use App\Models\Pago;
use App\Services\CarteraService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class PagosController extends Controller
{
    private const REQUIRED_IMPORT_COLUMNS = [
        'dni' => 'DNI',
        'operacion' => 'Operacion',
        'fecha' => 'Fecha',
        'moneda' => 'Moneda',
        'monto' => 'Monto',
        'gestor' => 'Gestor',
    ];

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

        $carteraId = $request->integer('cartera_id') ?: null;
        $buscar = trim((string) $request->query('buscar', $request->query('dni', '')));

        $query = Pago::query()
            ->with('cartera')
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        if ($carteraId) {
            $query->where('cartera_id', $carteraId);
        }

        if ($buscar !== '') {
            $query->where(function ($subquery) use ($buscar) {
                $subquery
                    ->where('dni', 'like', "%{$buscar}%")
                    ->orWhere('operacion', 'like', "%{$buscar}%")
                    ->orWhere('gestor', 'like', "%{$buscar}%");
            });
        }

        $pagos = $query->paginate(10)->appends($request->query());

        return view('pagos.index', compact('pagos', 'carteras', 'carteraId', 'buscar'));
    }

    public function legacy(string $tipo, CarteraService $carteras)
    {
        try {
            $cartera = $carteras->findBySlugOrFail($tipo);

            return redirect()->route('pagos.index', ['cartera_id' => $cartera->id]);
        } catch (Throwable) {
            return redirect()->route('pagos.index');
        }
    }

    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (array_values(self::REQUIRED_IMPORT_COLUMNS) as $index => $label) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
            $sheet->getColumnDimensionByColumn($index + 1)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'plantilla_pagos.xlsx');
    }

    public function store(StorePagoRequest $request)
    {
        try {
            Pago::query()->create($request->validated() + ['origen' => 'manual']);

            return back()->with('msg', 'Pago registrado correctamente.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $this->databaseErrorMessage($e, 'registrar el pago'));
        }
    }

    public function upload(ImportPagosRequest $request)
    {
        $data = $request->validated();
        $cartera = Cartera::query()->findOrFail($data['cartera_id']);

        try {
            [$insert, $errors] = $this->readImportRows(
                $request->file('archivo')->getRealPath(),
                (int) $cartera->id,
            );

            if ($errors !== []) {
                return back()->withInput()->with('error', $this->formatImportErrors($errors));
            }

            if ($insert === []) {
                return back()->withInput()->with('error', 'El archivo no contiene pagos validos para importar.');
            }

            DB::transaction(function () use ($insert) {
                foreach (array_chunk($insert, 500) as $chunk) {
                    Pago::query()->insert($chunk);
                }
            });

            return back()->with('msg', "Carga XLSX en {$cartera->nombre} completada: " . count($insert) . ' registros.');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $this->databaseErrorMessage($e, 'cargar el XLSX'));
        }
    }

    public function update(Request $request, Pago $pago)
    {
        $data = $request->validate([
            'cartera_id' => ['required', 'integer', 'exists:carteras,id'],
            'moneda' => ['required', 'string', 'max:20'],
            'fecha' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'gestor' => ['nullable', 'string', 'max:150'],
        ]);

        try {
            $pago->update($data);

            return back()->with('msg', 'Pago actualizado.');
        } catch (Throwable $e) {
            return back()->with('error', $this->databaseErrorMessage($e, 'actualizar el pago'));
        }
    }

    public function destroy(Pago $pago)
    {
        try {
            $pago->delete();

            return back()->with('msg', 'Pago eliminado.');
        } catch (Throwable $e) {
            return back()->with('error', $this->databaseErrorMessage($e, 'eliminar el pago'));
        }
    }

    private function databaseErrorMessage(Throwable $e, string $action): string
    {
        if ($e instanceof QueryException && str_contains($e->getMessage(), '1142')) {
            return "No se pudo {$action}: el usuario de base de datos no tiene permiso para escribir en la tabla pagos. Habilita INSERT y UPDATE sobre la base configurada.";
        }

        return "No se pudo {$action}: " . $e->getMessage();
    }

    private function readImportRows(string $path, int $carteraId): array
    {
        $spreadsheet = IOFactory::load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $headerRow = $rows[1] ?? null;

        if (!$headerRow) {
            throw new \InvalidArgumentException('El XLSX no tiene fila de encabezados.');
        }

        $headerMap = $this->buildHeaderMap($headerRow);
        $now = now();
        $insert = [];
        $errors = [];

        unset($rows[1]);

        foreach ($rows as $rowNumber => $row) {
            if ($this->isBlankRow($row, $headerMap)) {
                continue;
            }

            $rowErrors = [];
            $dni = $this->cellValue($row, $headerMap['dni']);
            $operacion = $this->cellValue($row, $headerMap['operacion']);
            $fecha = $this->parseFecha($row[$headerMap['fecha']] ?? null);
            $moneda = $this->cellValue($row, $headerMap['moneda']);
            $monto = $this->parseMonto($row[$headerMap['monto']] ?? null);
            $gestor = $this->cellValue($row, $headerMap['gestor']);

            if ($dni === '') {
                $rowErrors[] = "Fila {$rowNumber}: DNI obligatorio.";
            }

            if ($operacion === '') {
                $rowErrors[] = "Fila {$rowNumber}: Operacion obligatoria.";
            }

            if ($fecha === null) {
                $rowErrors[] = "Fila {$rowNumber}: Fecha invalida.";
            }

            if ($monto === null || $monto <= 0) {
                $rowErrors[] = "Fila {$rowNumber}: Monto debe ser numerico y mayor a cero.";
            }

            if ($rowErrors !== []) {
                array_push($errors, ...$rowErrors);
                continue;
            }

            $insert[] = [
                'cartera_id' => $carteraId,
                'dni' => $dni,
                'operacion' => $operacion,
                'fecha' => $fecha,
                'moneda' => $moneda !== '' ? $moneda : null,
                'monto' => $monto,
                'gestor' => $gestor !== '' ? $gestor : null,
                'origen' => 'xlsx',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return [$insert, $errors];
    }

    private function buildHeaderMap(array $headerRow): array
    {
        $available = [];

        foreach ($headerRow as $column => $label) {
            $normalized = $this->normalizeHeader($label);

            if ($normalized !== '') {
                $available[$normalized] = $column;
            }
        }

        $headerMap = [];
        $missing = [];

        foreach (self::REQUIRED_IMPORT_COLUMNS as $key => $label) {
            $normalized = $this->normalizeHeader($label);

            if (!isset($available[$normalized])) {
                $missing[] = $label;
                continue;
            }

            $headerMap[$key] = $available[$normalized];
        }

        if ($missing !== []) {
            throw new \InvalidArgumentException(
                'Faltan columnas obligatorias en el Excel: ' . implode(', ', $missing) . '.'
            );
        }

        return $headerMap;
    }

    private function isBlankRow(array $row, array $headerMap): bool
    {
        foreach ($headerMap as $column) {
            if ($this->cellValue($row, $column) !== '') {
                return false;
            }
        }

        return true;
    }

    private function cellValue(array $row, string $column): string
    {
        return trim((string) ($row[$column] ?? ''));
    }

    private function normalizeHeader(mixed $value): string
    {
        return Str::of((string) $value)
            ->ascii()
            ->lower()
            ->replace([' ', '_', '-', '.', ':'], '')
            ->toString();
    }

    private function formatImportErrors(array $errors): string
    {
        $visible = array_slice($errors, 0, 8);
        $suffix = count($errors) > count($visible)
            ? ' Hay ' . (count($errors) - count($visible)) . ' errores adicionales.'
            : '';

        return 'El archivo contiene errores: ' . implode(' ', $visible) . $suffix;
    }

    private function parseMonto(mixed $valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_numeric($valor)) {
            return (float) $valor;
        }

        $limpio = preg_replace('/[^\d,.\-]/', '', (string) $valor);

        if ($limpio === '') {
            return null;
        }

        if (str_contains($limpio, ',') && str_contains($limpio, '.')) {
            $limpio = strrpos($limpio, ',') > strrpos($limpio, '.')
                ? str_replace(',', '.', str_replace('.', '', $limpio))
                : str_replace(',', '', $limpio);
        } else {
            $limpio = str_replace(',', '.', $limpio);
        }

        return is_numeric($limpio) ? (float) $limpio : null;
    }

    private function parseFecha(mixed $valor): ?string
    {
        if ($valor instanceof \DateTimeInterface) {
            return $valor->format('Y-m-d');
        }

        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_numeric($valor)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $valor)->format('Y-m-d');
            } catch (Throwable) {
                return null;
            }
        }

        $valor = trim((string) $valor);
        $formatos = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y', 'm-d-Y', 'd/m/Y H:i:s', 'Y-m-d H:i:s'];

        foreach ($formatos as $formato) {
            try {
                $date = Carbon::createFromFormat($formato, $valor);
                return $date->toDateString();
            } catch (Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse(str_replace('/', '-', $valor))->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
