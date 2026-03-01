<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Addon::create([
            'name' => 'Premium Airport Transfer',
            'price' => 225000,
            'description' => 'Premium pickup/drop-off service from/to International Airport.'
        ]);
        \App\Models\Addon::create([
            'name' => 'Extra Bed',
            'price' => 300000,
            'description' => 'Comfortable extra bed with premium pillow set.'
        ]);
        \App\Models\Addon::create([
            'name' => 'Luxury Breakfast',
            'price' => 150000,
            'description' => 'Complete continental & traditional buffet breakfast.'
        ]);
    }
}
