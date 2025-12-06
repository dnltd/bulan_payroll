<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class TodayAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        $employees = Employee::all();

        foreach ($employees as $employee) {
            // Skip if attendance already exists for today
            if (Attendance::where('employee_id', $employee->id)->where('date', $today)->exists()) {
                continue;
            }

            if (in_array($employee->position, ['Driver', 'Conductor'])) {
                // Random 1-4 rounds for drivers/conductors
                $rounds = rand(1, 4);
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date'        => $today,
                    'rounds'      => $rounds,
                ]);
            } else {
                // Office staff: random time in/out
                $timeIn = Carbon::today()->addHours(rand(8, 9))->addMinutes(rand(0, 30));  // 8:00 - 9:30
                $timeOut = (clone $timeIn)->addHours(8)->addMinutes(rand(0, 30));          // 8-hour work + random

                Attendance::create([
                    'employee_id' => $employee->id,
                    'date'        => $today,
                    'time_in'     => $timeIn->format('H:i:s'),
                    'time_out'    => $timeOut->format('H:i:s'),
                ]);
            }
        }

        $this->command->info('Today attendance seeded successfully!');
    }
}
