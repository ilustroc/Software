<?php

namespace Database\Seeders;

use App\Models\Cartera;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class CarteraSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('carteras.items', []) as $item) {
            Cartera::query()->updateOrCreate(
                ['slug' => $item['slug']],
                Arr::only($item, ['nombre', 'codigo', 'sistema', 'orden']) + ['activa' => true],
            );
        }
    }
}
