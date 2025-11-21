<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tipificacion extends Model
{
    protected $table = 'tipificaciones';

    protected $fillable = [
        'tipificacion',
        'resultado',
        'mc',
        'peso',
        'orden',
    ];
}
