@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">Payroll Records</h4>

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="alert alert-primary">
                <strong>Total Payroll:</strong> ₱{{ number_format($totalPayroll, 2) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-danger">
                <strong>Total Deductions:</strong> ₱{{ number_format($totalDeductions, 2) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-success">
                <strong>Total Net:</strong> ₱{{ number_format($totalNet, 2) }}
            </div>
        </div>
    </div>

    <div class="mb-3">
    <a href="{{ route('admin.payslips.bulk') }}" class="btn btn-primary">
        <i class="bi bi-download"></i> Download All Payslips
    </a>
</div>


    <div class="card shadow-sm">
        <div class="card-body p-3 table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Gross Salary</th>
                        <th>Deductions</th>
                        <th>Net Salary</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $payroll)
                        <tr>
                            <td>{{ $payroll->employee->full_name ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($payroll->date)->format('M d, Y') }}</td>
                            <td>₱{{ number_format($payroll->gross_salary, 2) }}</td>
                            <td>₱{{ number_format($payroll->deductions, 2) }}</td>
                            <td>₱{{ number_format($payroll->net_salary, 2) }}</td>
                            <td>
                                <a href="{{ route('payslip.view', $payroll->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="{{ route('payslip.print', $payroll->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">Print</a>
                                <a href="{{ route('payslip.download', $payroll->id) }}" class="btn btn-sm btn-outline-success">Download</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No payroll data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $payrolls->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
