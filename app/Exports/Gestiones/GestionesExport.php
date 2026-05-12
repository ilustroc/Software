<?php

namespace App\Exports\Gestiones;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class GestionesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithChunkReading, WithColumnFormatting
{
    public function __construct(
        private ?int $carteraId,
        private string $desde,
        private string $hasta,
        private string $dni = '',
        private string $gestor = '',
    ) {
    }

    public function query()
    {
        $query = DB::table('gestiones as g')
            ->join('carteras as c', 'c.id', '=', 'g.cartera_id')
            ->select(
                'c.nombre as cartera',
                'g.documento',
                'g.nombre',
                'g.value2',
                'g.value1',
                'g.fullname',
                'g.operacion',
                'g.dateprocessed',
                'g.callerid',
                'g.comment',
                'g.monto_promesa',
                'g.nro_cuota',
                'g.fecha_promesa',
                'g.campaign',
            )
            ->whereBetween('g.dateprocessed', [$this->desde . ' 00:00:00', $this->hasta . ' 23:59:59']);

        if ($this->carteraId) {
            $query->where('g.cartera_id', $this->carteraId);
        }

        if ($this->dni !== '') {
            $query->where('g.documento', 'like', "%{$this->dni}%");
        }

        if ($this->gestor !== '') {
            $query->where('g.fullname', 'like', "%{$this->gestor}%");
        }

        return $query->orderByDesc('g.dateprocessed');
    }

    public function headings(): array
    {
        return [
            'Cartera',
            'Documento',
            'Nombre',
            'Value2',
            'Value1',
            'Fullname',
            'Operacion',
            'Dateprocessed',
            'Callerid',
            'Comment',
            'Monto promesa',
            'Nro cuota',
            'Fecha promesa',
            'Campana',
        ];
    }

    public function map($row): array
    {
        $formatDate = fn ($value) => $value ? Carbon::parse($value)->format('d/m/Y H:i') : '';

        return [
            $row->cartera ?? '',
            (string) ($row->documento ?? ''),
            $row->nombre ?? '',
            $row->value2 ?? '',
            $row->value1 ?? '',
            $row->fullname ?? '',
            (string) ($row->operacion ?? ''),
            $formatDate($row->dateprocessed ?? null),
            (string) ($row->callerid ?? ''),
            $row->comment ?? '',
            $row->monto_promesa ?? '',
            $row->nro_cuota ?? '',
            $formatDate($row->fecha_promesa ?? null),
            (string) ($row->campaign ?? ''),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function chunkSize(): int
    {
        return 2000;
    }
}
