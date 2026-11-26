<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class LingerieBrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'victorias-secret' => 'Victoria’s Secret',
            'calvin-klein' => 'Calvin Klein',
            'triumph' => 'Triumph',
            'chantelle' => 'Chantelle',
            'wacoal' => 'Wacoal',
            'intimissimi' => 'Intimissimi',
            'etam' => 'Etam',
            'aerie' => 'Aerie',
            'sloggi' => 'Sloggi',
            'simone-perele' => 'Simone Pérèle',
            'natori' => 'Natori',
            'la-perla' => 'La Perla',
        ];

        foreach ($brands as $slug => $name) {
            Brand::withTrashed()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'is_active' => false]
            );
        }
    }
}
