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
                'g.cliente',
                'g.socio',
                'g.tipificacion',
                'g.resultado',
                'g.asesor',
                'g.operacion',
                'g.entidad',
                'g.subcartera',
                'g.fecha_gestion',
                'g.fecha_agenda',
                'g.telefono',
                'g.comentario',
                'g.monto_promesa',
                'g.nro_cuotas',
                'g.fecha_promesa',
                'g.campaign',
            )
            ->whereBetween('g.fecha_gestion', [$this->desde . ' 00:00:00', $this->hasta . ' 23:59:59']);

        if ($this->carteraId) {
            $query->where('g.cartera_id', $this->carteraId);
        }

        if ($this->dni !== '') {
            $query->where('g.documento', 'like', "%{$this->dni}%");
        }

        if ($this->gestor !== '') {
            $query->where('g.asesor', 'like', "%{$this->gestor}%");
        }

        return $query->orderByDesc('g.fecha_gestion');
    }

    public function headings(): array
    {
        return [
            'Cartera',
            'Documento',
            'Cliente/Socio',
            'Tipificacion',
            'Resultado',
            'Gestor/Usuario',
            'Operacion',
            'Entidad',
            'Subcartera',
            'Fecha Gestion',
            'Fecha Agenda',
            'Telefono',
            'Comentario',
            'Monto promesa',
            'Nro cuotas',
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
            $row->cliente ?? $row->socio ?? '',
            $row->tipificacion ?? '',
            $row->resultado ?? '',
            $row->asesor ?? '',
            (string) ($row->operacion ?? ''),
            $row->entidad ?? '',
            $row->subcartera ?? '',
            $formatDate($row->fecha_gestion ?? null),
            $formatDate($row->fecha_agenda ?? null),
            (string) ($row->telefono ?? ''),
            $row->comentario ?? '',
            $row->monto_promesa ?? '',
            $row->nro_cuotas ?? '',
            $formatDate($row->fecha_promesa ?? null),
            (string) ($row->campaign ?? ''),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function chunkSize(): int
    {
        return 2000;
    }
}
