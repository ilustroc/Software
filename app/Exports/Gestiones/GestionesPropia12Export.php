<?php

namespace App\Exports\Gestiones;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class GestionesPropia12Export implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithChunkReading,
    WithColumnFormatting
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

        $q = DB::table('Gestiones_1y2')
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
            'Entidad',
            'Cartera',
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
            (string)($r->documento ?? ''),     // A = TEXTO
            (string)($r->nombre ?? ''),
            (string)($r->value2 ?? ''),
            (string)($r->value1 ?? ''),
            (string)($r->fullname ?? ''),
            (string)($r->operacion ?? ''),     // F = TEXTO
            (string)($r->entidad ?? ''),
            (string)($r->cartera ?? ''),
            $fmt($r->dateprocessed ?? null),
            $fmt($r->fechaAgenda ?? null),
            (string)($r->callerid ?? ''),      // K = TEXTO
            (string)($r->comment ?? ''),
            $r->pagar_por_cuota ?? '',
            $r->nroCuotas ?? '',
            $fmt($r->fecha_promesa ?? null),
            (string)($r->campaign ?? ''),
        ];
    }

    // Fuerza el formato TEXTO en Excel (evita notación científica y respeta ceros)
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // Documento
            'F' => NumberFormat::FORMAT_TEXT, // Operación
            'K' => NumberFormat::FORMAT_TEXT, // Teléfono
        ];
    }

    public function chunkSize(): int
    {
        return 2000;
    }
}
