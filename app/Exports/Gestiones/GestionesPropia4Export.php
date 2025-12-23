<?php

namespace App\Exports\Gestiones;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class GestionesPropia4Export implements FromQuery, WithHeadings, WithMapping, WithChunkReading, WithColumnFormatting
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
        $desdeFull = $this->desde . ' 00:00:00';
        $hastaFull = $this->hasta . ' 23:59:59';

        $q = DB::table('Gestiones_Propia4')
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

        return $q->orderBy('dateprocessed', 'desc');
    }

    public function headings(): array
    {
        return [
            'Cliente',
            'Documento',
            'Tipificación',
            'Resultado',
            'Usuario',
            'Operación',
            'Entidad',
            'Fecha Gestión',
            'Fecha Agenda',
            'Teléfono',
            'Comentario',
            'Importe financiamiento',
            'Nro Cuotas',
            'Fecha promesa',
            'Campaña',
        ];
    }

    public function map($r): array
    {
        $fmt = fn($v) => $v ? Carbon::parse($v)->format('d/m/Y H:i') : '';

        // Fuerza texto (evita que Excel lo convierta a número)
        $doc = (string) ($r->documento ?? '');
        $ope = (string) ($r->operacion ?? '');
        $tel = (string) ($r->callerid ?? '');

        return [
            $r->cliente ?? '',
            $doc,
            $r->value2 ?? '',
            $r->value1 ?? '',
            $r->fullname ?? '',
            $ope,
            $r->entidad ?? '',
            $fmt($r->dateprocessed ?? null),
            $fmt($r->fechaAgenda ?? null),
            $tel,
            $r->comment ?? '',
            $r->importe_financiamiento ?? '',
            $r->nroCuotas ?? '',
            $fmt($r->fecha_promesa ?? null),
            $r->campaign ?? '',
        ];
    }

    public function columnFormats(): array
    {
        // B = Documento, F = Operación, J = Teléfono
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function chunkSize(): int
    {
        return 2000;
    }
}
