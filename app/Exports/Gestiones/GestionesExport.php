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
        private string $tabla,
        private string $desde,
        private string $hasta,
        private string $documento = '',
        private string $tipificacion = ''
    ) {}

    public function query()
    {
        $desdeFull = $this->desde . ' 00:00:00';
        $hastaFull = $this->hasta . ' 23:59:59';

        $q = DB::table($this->tabla)
            ->whereBetween('dateprocessed', [$desdeFull, $hastaFull]);

        if ($this->documento !== '') {
            $q->where('documento', 'like', "%{$this->documento}%");
        }

        if ($this->tipificacion !== '') {
            $q->where('value2', 'like', "%{$this->tipificacion}%");
        }

        return $q->orderByDesc('dateprocessed');
    }

    public function headings(): array
    {
        // Encabezado universal para las 3 tablas
        return [
            'Documento', 'Nombre/Cliente', 'Tipificación', 'Resultado', 'Gestor/Usuario',
            'Operación', 'Entidad', 'Cartera/CTL', 'Fecha Gestión', 'Fecha Agenda',
            'Teléfono', 'Comentario', 'Pagar/Importe', 'Nro Cuotas', 'Fecha promesa', 'Campaña'
        ];
    }

    public function map($r): array
    {
        $fmt = fn($v) => $v ? Carbon::parse($v)->format('d/m/Y H:i') : '';

        return [
            (string)($r->documento ?? ''),
            $r->nombre ?? $r->cliente ?? '',
            $r->value2 ?? '',
            $r->value1 ?? '',
            $r->fullname ?? '',
            (string)($r->operacion ?? ''),
            $r->entidad ?? $r->ctl ?? '',
            $r->cartera ?? '',
            $fmt($r->dateprocessed ?? null),
            $fmt($r->fechaAgenda ?? null),
            (string)($r->callerid ?? ''),
            $r->comment ?? '',
            $r->pagar_por_cuota ?? $r->importe_financiamiento ?? '',
            $r->nroCuotas ?? '',
            $fmt($r->fecha_promesa ?? null),
            (string)($r->campaign ?? ''),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // Documento
            'F' => NumberFormat::FORMAT_TEXT, // Operación
            'K' => NumberFormat::FORMAT_TEXT, // Teléfono
        ];
    }

    public function chunkSize(): int { return 2000; }
}