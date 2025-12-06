<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Services\PayrollService;

class PayrollController extends Controller
{
    protected PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function index(Request $request)
{
    // Default: LAST WEEK (Sat–Fri)
    $startDate = $request->input('start_date')
        ? Carbon::parse($request->input('start_date'))->format('Y-m-d')
        : now()->subWeek()->startOfWeek(Carbon::SATURDAY)->format('Y-m-d');

    $endDate = $request->input('end_date')
        ? Carbon::parse($request->input('end_date'))->format('Y-m-d')
        : Carbon::parse($startDate)->addDays(6)->format('Y-m-d');

    $query = Payroll::with('employee.salaryRate')
        ->where('start_date', $startDate)
        ->where('end_date', $endDate);

    // ✅ Filter by position
    if ($request->filled('position')) {
        $query->whereHas('employee', function ($q) use ($request) {
            $q->where('position', $request->position);
        });
    }

    // ✅ Preferred: filter by employee_id (from autocomplete)
    if ($request->filled('employee_id')) {
        $query->where('employee_id', $request->employee_id);
    }
    // ✅ Fallback: filter by search string
    elseif ($request->filled('search')) {
        $search = $request->input('search');
        $query->whereHas('employee', function ($q) use ($search) {
            $q->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$search}%"]);
        });
    }

    $payrolls = $query->orderBy('id', 'desc')->paginate(10);

    // Totals query (respect same filters)
    $totalsQuery = Payroll::query()
        ->where('start_date', $startDate)
        ->where('end_date', $endDate);

    if ($request->filled('position')) {
        $totalsQuery->whereHas('employee', function ($q) use ($request) {
            $q->where('position', $request->position);
        });
    }

    if ($request->filled('employee_id')) {
        $totalsQuery->where('employee_id', $request->employee_id);
    } elseif ($request->filled('search')) {
        $search = $request->input('search');
        $totalsQuery->whereHas('employee', function ($q) use ($search) {
            $q->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$search}%"]);
        });
    }

    $totalPayroll    = (float) $totalsQuery->sum('gross_salary');
    $totalDeductions = (float) $totalsQuery->sum('deductions');
    $totalNet        = (float) $totalsQuery->sum('net_salary');

    return view('admin.payroll.index', compact(
        'payrolls',
        'totalPayroll',
        'totalDeductions',
        'totalNet',
        'startDate',
        'endDate'
    ));
}


    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date')
            ?? now()->subWeek()->startOfWeek(Carbon::SATURDAY)->format('Y-m-d');
        $endDate = $request->input('end_date')
            ?? Carbon::parse($startDate)->addDays(6)->format('Y-m-d');

        $payrolls = Payroll::with('employee.salaryRate')
            ->where('start_date', $startDate)
            ->where('end_date', $endDate)
            ->latest('id')
            ->get();

        return Pdf::loadView('admin.payroll.pdf', compact('payrolls'))
            ->setPaper('a4', 'landscape')
            ->download("payroll_report_{$startDate}_to_{$endDate}.pdf");
    }

    public function print(Request $request)
    {
        $startDate = $request->input('start_date')
            ?? now()->subWeek()->startOfWeek(Carbon::SATURDAY)->format('Y-m-d');
        $endDate = $request->input('end_date')
            ?? Carbon::parse($startDate)->addDays(6)->format('Y-m-d');

        $payrolls = Payroll::with('employee.salaryRate')
            ->where('start_date', $startDate)
            ->where('end_date', $endDate)
            ->latest('id')
            ->get();

        return response()->view('admin.payroll.print', compact('payrolls'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function generate()
    {
        // Generate for current week, not last week
        $startDate = now()->startOfWeek(Carbon::SATURDAY)->format('Y-m-d');
        $endDate   = now()->startOfWeek(Carbon::SATURDAY)->addDays(6)->format('Y-m-d');

        $this->payrollService->generatePayroll($startDate, $endDate);

        return redirect()->route('admin.payroll.index')
            ->with('success', "Payroll generated for $startDate to $endDate.");
    }
}
