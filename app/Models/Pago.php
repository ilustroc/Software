<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    use SoftDeletes;

    protected $table = 'pagos';

    protected $fillable = [
        'cartera_id',
        'dni',
        'operacion',
        'moneda',
        'fecha',
        'monto',
        'gestor',
        'origen',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function cartera(): BelongsTo
    {
        return $this->belongsTo(Cartera::class);
    }
}
