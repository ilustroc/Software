<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cartera extends Model
{
    protected $table = 'carteras';

    protected $fillable = [
        'slug',
        'nombre',
        'codigo',
        'sistema',
        'activa',
        'orden',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'sistema' => 'integer',
        'orden' => 'integer',
    ];

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function gestiones(): HasMany
    {
        return $this->hasMany(Gestion::class);
    }

    public function scopeActiva(Builder $query): Builder
    {
        return $query->where('activa', true);
    }
}
