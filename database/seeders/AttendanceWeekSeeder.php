<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\RoundTrip;
use App\Models\Employee;
use Carbon\Carbon;

class AttendanceWeekSeeder extends Seeder
{
    public function run(): void
    {
        // Exclude System Administrator
        $employees = Employee::where('position', '!=', 'System Administrator')->get();

        // Fixed payroll week: Sep 13 (Sat) → Sep 19 (Fri), 2025
        $start = Carbon::create(2025, 9, 13); // Saturday
        $end   = Carbon::create(2025, 9, 19); // Friday

        foreach ($employees as $employee) {
            $date = $start->copy();

            while ($date <= $end) {

                if (in_array($employee->position, ['Driver', 'Conductor'])) {
                    // === Drivers & Conductors (Rounds-based OT) ===
                    $timeIn  = '06:00:00';
                    $timeOut = '18:00:00';

                    // OT based on day
                    $rounds = match (true) {
                        $date->isSaturday(), $date->isMonday(), $date->isWednesday() => 4, // heavy OT
                        $date->isFriday() => 3, // medium OT
                        default => 2, // regular
                    };

                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date'        => $date->toDateString(),
                        'time_in'     => $timeIn,
                        'time_out'    => $timeOut,
                        'rounds'      => $rounds,
                    ]);

                    // Round trips based on rounds
                    for ($i = 1; $i <= $rounds; $i++) {
                        $departure = Carbon::parse($timeIn)->addHours(($i - 1) * 3);
                        $arrival   = $departure->copy()->addHour();
                        $return    = $arrival->copy()->addMinutes(30);

                        RoundTrip::create([
                            'employee_id' => $employee->id,
                            'date'        => $date->toDateString(),
                            'departure'   => $departure->format('H:i:s'),
                            'arrival'     => $arrival->format('H:i:s'),
                            'return'      => $return->format('H:i:s'),
                            'status'      => 'completed',
                            'round_number'=> $i,
                        ]);
                    }

                } else {
                    // === Office Roles (Hours-based OT) ===
                    $timeIn = $date->copy()->setTime(8, 0, 0);

                    // OT Tue, Thu, Fri
                    $timeOut = match (true) {
                        $date->isTuesday(), $date->isThursday() => $date->copy()->setTime(19, 0, 0), // 11 hrs
                        $date->isFriday() => $date->copy()->setTime(18, 0, 0), // 10 hrs
                        default => $date->copy()->setTime(17, 0, 0), // normal 9 hrs
                    };

                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date'        => $date->toDateString(),
                        'time_in'     => $timeIn->format('H:i:s'),
                        'time_out'    => $timeOut->format('H:i:s'),
                    ]);
                }

                $date->addDay();
            }
        }
    }
}
