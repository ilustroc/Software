<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Llamada extends Model
{
    protected $table = 'llamadas';

    protected $fillable = [
        'dni',
        'telefono',
        'resultado_gestion',
        'resultado',
        'fecha_gestion',
        'observaciones',
        'fuente',
        'metadata',
    ];

    protected $casts = [
        'fecha_gestion' => 'datetime',
        'metadata' => 'array',
    ];
}
