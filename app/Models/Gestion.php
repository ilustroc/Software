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
        'nombre',
        'value2',
        'value1',
        'fullname',
        'operacion',
        'dateprocessed',
        'callerid',
        'comment',
        'monto_promesa',
        'nro_cuota',
        'fecha_promesa',
        'campaign',
        'origen',
        'legacy_id',
    ];

    protected $casts = [
        'dateprocessed' => 'datetime',
        'fecha_promesa' => 'datetime',
        'monto_promesa' => 'decimal:2',
    ];

    public function cartera(): BelongsTo
    {
        return $this->belongsTo(Cartera::class);
    }
}
