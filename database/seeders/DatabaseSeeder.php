<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SalaryRateSeeder::class,
            EmployeeSeeder::class,
            AttendancesAndRoundTripsSeeder::class,
            HolidaySeeder::class,
            AttendanceWeekSeeder::class,
            AttendanceSeeder::class,
            AttendanceToday::class,
        ]);
    }
}
