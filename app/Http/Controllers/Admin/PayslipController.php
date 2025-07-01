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
        $pdf = Pdf::loadView('admin.payslips.pdf', compact('payroll'));
        return $pdf->stream('Payslip_' . ($payroll->employee->full_name ?? 'Employee') . '.pdf');
    }

    public function download($id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);
        $pdf = Pdf::loadView('admin.payslips.pdf', compact('payroll'));
        return $pdf->download('Payslip_' . ($payroll->employee->full_name ?? 'Employee') . '.pdf');
    }

    public function bulkDownload()
    {
        $payrolls = Payroll::with('employee')
            ->whereDate('date', now()->format('Y-m-d'))
            ->get();

        if ($payrolls->isEmpty()) {
            return back()->with('error', 'No payrolls found for today.');
        }

        $pdf = Pdf::loadView('admin.payslips.bulk', compact('payrolls'));
        return $pdf->download('All_Payslips_' . now()->format('Y_m_d') . '.pdf');
    }
}
