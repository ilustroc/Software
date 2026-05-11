<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('carteras')) {
            return;
        }

        $now = now();
        $carteras = [
            ['slug' => 'propia12', 'nombre' => 'Propia 1 y 2', 'codigo' => 'P12', 'sistema' => 1, 'orden' => 10],
            ['slug' => 'propia3', 'nombre' => 'Propia 3', 'codigo' => 'P3', 'sistema' => 3, 'orden' => 20],
            ['slug' => 'kp-invest', 'nombre' => 'KP Invest', 'codigo' => 'KPI', 'sistema' => 4, 'orden' => 30],
            ['slug' => 'apdayc', 'nombre' => 'APDAYC', 'codigo' => 'APD', 'sistema' => null, 'orden' => 40],
        ];

        foreach ($carteras as $cartera) {
            DB::table('carteras')->updateOrInsert(
                ['slug' => $cartera['slug']],
                $cartera + [
                    'activa' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    public function down(): void
    {
        // No-op: keep cartera configuration safe in shared/production databases.
    }
};
