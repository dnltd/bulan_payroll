<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;

class HolidaySeeder extends Seeder
{
    public function run()
    {
        // Existing holiday
        Holiday::updateOrCreate([
            'date' => '2025-09-10',
        ], [
            'name' => 'Special Holiday',
        ]);

        // New holiday between October 4 and October 10
        Holiday::updateOrCreate([
            'date' => '2025-10-05',
        ], [
            'name' => 'Extra Holiday',
        ]);

        // Another holiday between October 4 and October 10
        Holiday::updateOrCreate([
            'date' => '2025-10-08',
        ], [
            'name' => 'Bulan Transport Cooperative Anniversary',
        ]);
    }
}
