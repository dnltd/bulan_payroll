<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Deduction;
use App\Models\Attendance;
use App\Models\Holiday;
use Carbon\Carbon;
use DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalEmployees = Employee::count();
        $totalPayroll = Payroll::sum('gross_salary');
        $totalDeductions = Deduction::sum('amount');
        $totalAttendance = Attendance::whereDate('date', Carbon::today())->count();

        $weeklyPayroll = Payroll::select(
            DB::raw('DATE(date) as day'),
            DB::raw('SUM(gross_salary) as total')
        )
        ->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
        ->groupBy('day')
        ->get();

        $weekLabels = $weeklyPayroll->pluck('day')->map(fn($date) => Carbon::parse($date)->format('D'));
        $weeklyPayrollAmounts = $weeklyPayroll->pluck('total');

        $recentHolidays = Holiday::whereDate('date', '>=', Carbon::today())
                                ->orderBy('date', 'asc')
                                ->take(5)
                                ->get();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'totalPayroll',
            'totalDeductions',
            'totalAttendance',
            'weekLabels',
            'weeklyPayrollAmounts',
            'recentHolidays'
        ));
    }

    // Add other admin methods here as needed
}
