<!DOCTYPE html>
<html>
<head>
    <title>Print Deduction Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; }
        .text-center { text-align: center; }
        .table { margin-top: 20px; width: 100%; }
        .mt-5 { margin-top: 3rem; }
        .mb-5 { margin-bottom: 3rem; }
    </style>
</head>
<body onload="window.print(); setTimeout(() => window.close(), 1000);">

<div class="text-center mb-4">
    <img src="{{ asset('images/logo.png') }}" alt="Company Logo" style="width: 80px;">
    <h4 class="mt-2 mb-0">Bulan Transport Cooperative</h4>
    <p class="mb-0">Deduction Report</p>
    <p><strong>Date:</strong> {{ $date }}</p>
</div>

<table class="table table-bordered">
    <thead class="table-light">
        <tr>
            <th>Employee</th>
            <th>Type</th>
            <th>Amount (₱)</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($deductions as $deduction)
            <tr>
                <td>{{ $deduction->employee->full_name }}</td>
                <td>{{ $deduction->type }}</td>
                <td>₱{{ number_format($deduction->amount, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($deduction->date)->toFormattedDateString() }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center">No deductions found.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="text-center mt-5">
    <p class="mb-5">Prepared by:</p>
    <div style="margin: auto; width: 200px; border-top: 1px solid #000; padding-top: 5px;">
        Admin Signature
    </div>
</div>

</body>
</html>
