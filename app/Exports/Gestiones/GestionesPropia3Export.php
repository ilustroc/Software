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

class GestionesPropia3Export implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithChunkReading, WithColumnFormatting
{
    public function __construct(
        private string $desde,
        private string $hasta,
        private string $telefono = '',
        private string $documento = '',
        private string $tipificacion = ''
    ) {}

    public function query()
    {
        $desdeFull = $this->desde.' 00:00:00';
        $hastaFull = $this->hasta.' 23:59:59';

        $q = DB::table('Gestiones_Propia3')
            ->whereBetween('dateprocessed', [$desdeFull, $hastaFull]);

        if ($this->telefono !== '') {
            $q->where('callerid', 'like', "%{$this->telefono}%");
        }

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
        return [
            'Documento',
            'Nombre',
            'Tipificación',
            'Resultado',
            'Gestor',
            'Operación',
            'CTL',
            'Fecha Gestión',
            'Fecha Agenda',
            'Teléfono',
            'Comentario',
            'Pagar por cuota',
            'Nro Cuotas',
            'Fecha promesa',
            'Campaña',
        ];
    }

    public function map($r): array
    {
        $fmt = fn($v) => $v ? Carbon::parse($v)->format('d/m/Y H:i') : '';

        return [
            (string) ($r->documento ?? ''),   // texto
            $r->nombre ?? '',
            $r->value2 ?? '',
            $r->value1 ?? '',
            $r->fullname ?? '',
            (string) ($r->operacion ?? ''),   // texto
            $r->ctl ?? '',
            $fmt($r->dateprocessed ?? null),
            $fmt($r->fechaAgenda ?? null),
            (string) ($r->callerid ?? ''),    // texto
            $r->comment ?? '',
            $r->pagar_por_cuota ?? '',
            $r->nroCuotas ?? '',
            $fmt($r->fecha_promesa ?? null),
            $r->campaign ?? '',
        ];
    }

    // Fuerza formato TEXTO en Excel
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // Documento
            'F' => NumberFormat::FORMAT_TEXT, // Operación
            'J' => NumberFormat::FORMAT_TEXT, // Teléfono
        ];
    }

    public function chunkSize(): int
    {
        return 2000;
    }
}
