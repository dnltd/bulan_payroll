<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Deduction;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index()
    {
        $payrolls = Payroll::with('employee')->orderBy('date', 'desc')->paginate(10);

        // Totals for the view
        $totalPayroll = $payrolls->sum('gross_salary');
        $totalDeductions = $payrolls->sum('deductions');
        $totalNet = $payrolls->sum('net_salary');

        return view('admin.payroll.index', compact('payrolls', 'totalPayroll', 'totalDeductions', 'totalNet'));
    }

    public function generate()
    {
        $employees = Employee::with('salaryRate')->get();
        $holidays = Holiday::pluck('date')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();

        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        foreach ($employees as $employee) {
            $dailyRate = $employee->salaryRate->daily_rate ?? 0;
            $totalGross = 0;

            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            foreach ($attendances as $attendance) {
                $attendanceDate = Carbon::parse($attendance->date)->toDateString();
                $isHoliday = in_array($attendanceDate, $holidays);
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
                'date' => now(),
            ]);
        }

        return redirect()->route('admin.payroll.index')->with('success', 'Payroll generated successfully.');
    }
}
