<?php

namespace App\Exports\Pagos;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;

use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class ReportePagosExport extends DefaultValueBinder implements
    FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithChunkReading, WithCustomValueBinder
{
    public function __construct(
        private string $desde,
        private string $hasta,
        private string $agente = '',
        private string $cartera = '' // '' = todas
    ) {}

    public function query()
    {
        $q1 = DB::table('Pagos_1y2')->selectRaw("
            'Propia 1 y 2' as cartera,
            DNI, OPERACION, MONEDA, FECHA, MONTO, GESTOR
        ");

        $q2 = DB::table('Pagos_3')->selectRaw("
            'Propia 3' as cartera,
            DNI, OPERACION, MONEDA, FECHA, MONTO, GESTOR
        ");

        $q3 = DB::table('Pagos_4')->selectRaw("
            'Propia 4' as cartera,
            DNI, OPERACION, MONEDA, FECHA, MONTO, GESTOR
        ");

        $union = $q1->unionAll($q2)->unionAll($q3);

        $q = DB::query()
            ->fromSub($union, 'p')
            ->select('p.*')
            ->whereBetween('p.FECHA', [$this->desde, $this->hasta]);

        if ($this->agente !== '') {
            $q->where('p.GESTOR', 'like', "%{$this->agente}%");
        }

        if ($this->cartera !== '') {
            $q->where('p.cartera', $this->cartera);
        }

        return $q->orderByDesc('p.FECHA');
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Cartera',
            'DNI',
            'Operación',
            'Moneda',
            'Monto',
            'Gestor',
        ];
    }

    public function map($r): array
    {
        $fecha = $r->FECHA ?? $r->fecha ?? null;

        return [
            $fecha ? Carbon::parse($fecha)->format('d/m/Y') : '',
            $r->cartera ?? '',
            (string) ($r->DNI ?? $r->dni ?? ''),               // TEXTO
            (string) ($r->OPERACION ?? $r->operacion ?? ''),   // TEXTO
            $r->MONEDA ?? $r->moneda ?? '',
            (float) ($r->MONTO ?? $r->monto ?? 0),
            $r->GESTOR ?? $r->gestor ?? '',
        ];
    }

    // Fuerza tipo TEXTO en columnas C (DNI) y D (Operación)
    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), ['C', 'D'], true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

    public function chunkSize(): int
    {
        return 2000;
    }
}
