<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\RoundTrip;
use App\Models\Employee;
use Carbon\Carbon;

class AttendanceToday extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        // Get all employees except System Administrator
        $employees = Employee::where('position', '!=', 'System Administrator')->get();

        foreach ($employees as $employee) {
            if (in_array($employee->position, ['Driver', 'Conductor'])) {
                $timeIn = '06:00:00';

                // ✅ Drivers = 1 round, Conductors = 2 rounds
                $rounds = $employee->position === 'Driver' ? 1 : 2;

                // Create attendance (no time_out)
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date'        => $today->toDateString(),
                    'time_in'     => $timeIn,
                    'time_out'    => null,
                    'rounds'      => $rounds,
                ]);

                // Add round trips
                for ($i = 1; $i <= $rounds; $i++) {
                    $departure = Carbon::parse($timeIn)->addHours(($i - 1) * 2);
                    $arrival   = $departure->copy()->addHour();
                    $return    = $arrival->copy()->addMinutes(30);

                    RoundTrip::create([
                        'employee_id'  => $employee->id,
                        'date'         => $today->toDateString(),
                        'departure'    => $departure->format('H:i:s'),
                        'arrival'      => $arrival->format('H:i:s'),
                        'return'       => $return->format('H:i:s'),
                        'status'       => 'completed',
                        'round_number' => $i,
                    ]);
                }
            } else {
                // === Office staff (no time_out yet)
                Attendance::create([
                    'employee_id' => $employee->id,
                    'date'        => $today->toDateString(),
                    'time_in'     => '08:00:00',
                    'time_out'    => null,
                    'rounds'      => null,
                ]);
            }
        }

        echo "✅ Today's attendance seeded — Drivers: 1 round, Conductors: 2 rounds, Office: no time_out.\n";
    }
}
