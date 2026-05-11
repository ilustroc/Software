<?php

namespace App\Services;

use App\Models\Cartera;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CarteraService
{
    public static function normalizeSlug(string $slug): string
    {
        $key = Str::of($slug)->trim()->lower()->replace('_', '-')->toString();

        return config("carteras.aliases.$key", $key);
    }

    public function findBySlugOrFail(string $slug): Cartera
    {
        return Cartera::query()
            ->where('slug', self::normalizeSlug($slug))
            ->firstOrFail();
    }

    public function active(): Collection
    {
        return Cartera::query()
            ->activa()
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
    }

    public function definitionFor(string $slug): ?array
    {
        $normalized = self::normalizeSlug($slug);

        return collect(config('carteras.items', []))
            ->firstWhere('slug', $normalized);
    }
}
