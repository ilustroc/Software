<?php

namespace App\Exports\Pagos;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class ReportePagosExport extends DefaultValueBinder implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithChunkReading,
    WithCustomValueBinder
{
    public function __construct(
        private string $desde,
        private string $hasta,
        private ?int $carteraId = null,
        private string $dni = '',
        private string $gestor = '',
    ) {
    }

    public function query()
    {
        $query = DB::table('pagos')
            ->join('carteras', 'carteras.id', '=', 'pagos.cartera_id')
            ->select(
                'pagos.fecha',
                'carteras.nombre as cartera',
                'pagos.dni',
                'pagos.operacion',
                'pagos.moneda',
                'pagos.monto',
                'pagos.gestor',
            )
            ->whereNull('pagos.deleted_at')
            ->whereBetween('pagos.fecha', [$this->desde, $this->hasta]);

        if ($this->carteraId) {
            $query->where('pagos.cartera_id', $this->carteraId);
        }

        if ($this->dni !== '') {
            $query->where('pagos.dni', 'like', "%{$this->dni}%");
        }

        if ($this->gestor !== '') {
            $query->where('pagos.gestor', 'like', "%{$this->gestor}%");
        }

        return $query->orderByDesc('pagos.fecha')->orderByDesc('pagos.id');
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Cartera',
            'DNI',
            'Operacion',
            'Moneda',
            'Monto',
            'Gestor',
        ];
    }

    public function map($row): array
    {
        return [
            $row->fecha ? Carbon::parse($row->fecha)->format('d/m/Y') : '',
            $row->cartera ?? '',
            (string) ($row->dni ?? ''),
            (string) ($row->operacion ?? ''),
            $row->moneda ?? '',
            (float) ($row->monto ?? 0),
            $row->gestor ?? '',
        ];
    }

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
