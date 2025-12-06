<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payslip;
use App\Models\Payroll;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PayslipController extends Controller
{
    public function view($id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);
        return view('admin.payslips.pdf', compact('payroll'));
    }

    public function print($id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);
        return view('admin.payslips.print', compact('payroll'));
    }

    public function download($id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);

        $pdf = Pdf::loadView('admin.payslips.pdf', compact('payroll'))
            ->setPaper([0, 0, 280.63, 595.28], 'portrait'); // ✅ DL size in points

        return $pdf->download('Payslip_' . ($payroll->employee->full_name ?? 'Employee') . '.pdf');
    }

    public function bulkPrint(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        if (!$startDate || !$endDate) {
            // Default: current week (Sat–Fri)
            $today     = now();
            $startDate = $today->copy()->startOfWeek(Carbon::SATURDAY)->format('Y-m-d');
            $endDate   = $today->copy()->endOfWeek(Carbon::FRIDAY)->format('Y-m-d');
        }

        $payrolls = Payroll::with('employee')
            ->whereDate('start_date', $startDate)
            ->whereDate('end_date', $endDate)
            ->get();

        if ($payrolls->isEmpty()) {
            return back()->with('error', "No payrolls found for the selected week.");
        }

        return view('admin.payslips.bulk', compact('payrolls', 'startDate', 'endDate'));
    }
}
