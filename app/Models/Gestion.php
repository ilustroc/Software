<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gestion extends Model
{
    protected $table = 'gestiones';

    protected $fillable = [
        'cartera_id',
        'documento',
        'licencia_id',
        'socio',
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
        'origen',
        'metadata',
    ];

    protected $casts = [
        'fecha_gestion' => 'datetime',
        'fecha_agenda' => 'datetime',
        'fecha_promesa' => 'datetime',
        'monto_promesa' => 'decimal:2',
        'nro_cuotas' => 'integer',
        'metadata' => 'array',
    ];

    public function cartera(): BelongsTo
    {
        return $this->belongsTo(Cartera::class);
    }
}
