<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Deduction;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\RoundTrip;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today()->format('Y-m-d');

        // ✅ Total employees
        $totalEmployees = Employee::count();

        // ✅ Find the latest payroll record (weekly period)
        $latestPayroll = Payroll::orderBy('end_date', 'desc')->first();
        $payrollStart  = $latestPayroll ? $latestPayroll->start_date : Carbon::now()->startOfWeek();
        $payrollEnd    = $latestPayroll ? $latestPayroll->end_date   : Carbon::now()->endOfWeek();

        // ✅ Payrolls exactly for that period
        $currentPayrolls = Payroll::with('employee.deductions')
            ->whereDate('start_date', $payrollStart)
            ->whereDate('end_date', $payrollEnd)
            ->get();

        // ✅ Total payroll (gross) in that period
        $totalPayroll = $currentPayrolls->sum('gross_salary');

        // ✅ Total deductions in that period
        $totalDeductions = $currentPayrolls->sum(function ($payroll) {
            $start = $payroll->start_date;
            $end   = $payroll->end_date;
            return $payroll->employee->deductions()
                        ->whereBetween('date', [$start, $end])
                        ->sum('amount');
        });

        // ✅ Attendance today
        $presentCount = Attendance::whereDate('date', $today)
            ->distinct('employee_id')
            ->count('employee_id');

        $absentCount = max($totalEmployees - $presentCount, 0);

        // ✅ Group deductions by type within payroll period
        $deductionsByType = Deduction::select('type', DB::raw('SUM(amount) as total'))
            ->whereBetween('date', [$payrollStart, $payrollEnd])
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // ✅ Upcoming holidays
        $recentHolidays = Holiday::whereDate('date', '>=', $today)
            ->orderBy('date', 'asc')
            ->take(5)
            ->get();

        // ✅ Payroll distribution by role/position (Net Salary)
        $payrollDistribution = Payroll::join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->select('employees.position', DB::raw('SUM(payrolls.gross_salary - payrolls.deductions) as total_net'))
            ->whereBetween('payrolls.start_date', [$payrollStart, $payrollEnd])
            ->groupBy('employees.position')
            ->pluck('total_net', 'employees.position')
            ->toArray();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'totalPayroll',
            'totalDeductions',
            'presentCount',
            'absentCount',
            'recentHolidays',
            'deductionsByType',
            'payrollStart',
            'payrollEnd',
            'payrollDistribution'
        ));
    }


    public function autocomplete(Request $request)
    {
        $term = $request->get('term', '');
        $context = $request->get('context', 'employees');

        $employeeIds = collect();

        if ($context === 'employees') {
            $employeeIds = Employee::whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$term}%"])
                ->pluck('id');
        } elseif ($context === 'payroll') {
            $employeeIds = Payroll::whereHas('employee', function($q) use ($term) {
                $q->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$term}%"]);
            })->pluck('employee_id');
        } elseif ($context === 'attendance') {
            $employeeIds = Attendance::whereHas('employee', function($q) use ($term) {
                $q->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$term}%"]);
            })->pluck('employee_id');
        } elseif ($context === 'deductions') {
            $employeeIds = Deduction::whereHas('employee', function($q) use ($term) {
                $q->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$term}%"]);
            })->pluck('employee_id');
        } elseif ($context === 'round_trip') {
            $employeeIds = RoundTrip::whereHas('employee', function($q) use ($term) {
                $q->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$term}%"]);
            })->pluck('employee_id');
        }

        // ✅ Now fetch only unique employees
        $employees = Employee::whereIn('id', $employeeIds->unique())
            ->limit(8)
            ->get()
            ->map(fn($emp) => [
                'id'   => $emp->id,
                'name' => $emp->full_name,
            ]);

        return response()->json($employees);
    }
}
