<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Coupon::create([
            'code' => 'WELCOME10',
            'discount_amount' => 10,
            'discount_type' => 'percent',
            'is_active' => true
        ]);
        \App\Models\Coupon::create([
            'code' => 'OASIS50',
            'discount_amount' => 50000,
            'discount_type' => 'fixed',
            'is_active' => true
        ]);
    }
}
