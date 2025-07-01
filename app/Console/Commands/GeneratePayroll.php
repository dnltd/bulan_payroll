<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Payroll;
use App\Models\Deduction;
use Carbon\Carbon;

class GeneratePayroll extends Command
{
    protected $signature = 'payroll:generate';
    protected $description = 'Automatically generate weekly payroll with holiday pay';

    public function handle()
    {
        $employees = Employee::with('salaryRate')->get();
        $holidays = Holiday::pluck('date')->toArray();

        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        foreach ($employees as $employee) {
            $dailyRate = $employee->salaryRate->daily_rate ?? 0;
            $totalGross = 0;

            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            foreach ($attendances as $attendance) {
                $isHoliday = in_array(Carbon::parse($attendance->date)->toDateString(), $holidays);
                $rate = $isHoliday ? ($dailyRate * 2) : $dailyRate;
                $totalGross += $rate;
            }

            $deductions = Deduction::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->sum('amount');

            $net = $totalGross - $deductions;

            Payroll::create([
                'employee_id' => $employee->id,
                'gross_salary' => $totalGross,
                'deductions' => $deductions,
                'net_salary' => $net,
                'date' => now()
            ]);
        }

        $this->info('Payroll generated successfully.');
    }
}
