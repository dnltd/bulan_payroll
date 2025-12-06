<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalaryRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('salary_rates')->insert([
            [
                'position'   => 'Driver',
                'daily_rate' => 430,
                'overtime'   => 215, 
            ],
            [
                'position'   => 'Conductor',
                'daily_rate' => 395,
                'overtime'   => 197.5,
            ],
            [
                'position'   => 'Dispatcher',
                'daily_rate' => 550,
                'overtime'   => 50,
            ],
            [
                'position'   => 'Cashier',
                'daily_rate' => 500,
                'overtime'   => 50,
            ],
            [
                'position'   => 'Treasurer',
                'daily_rate' => 500,
                'overtime'   => 50,
            ],
            [
                'position'   => 'Secretary',
                'daily_rate' => 500,
                'overtime'   => 50,
            ],
            [
                'position'   => 'General Manager',
                'daily_rate' => 500,
                'overtime'   => 50,
            ],
        ]);
    }
}
