<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deduction Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            color: #000;
            margin: 30px;
        }

        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .report-header img {
            max-width: 80px;
            margin-bottom: 10px;
        }

        .report-header h2 {
            margin: 0;
            font-size: 20px;
        }

        .report-header p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }

        thead {
            background-color: #f2f2f2;
        }

        .signature-section {
            margin-top: 60px;
            text-align: center;
        }

        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #000;
            width: 200px;
            margin-left: auto;
            margin-right: auto;
            padding-top: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="report-header">
    <img src="{{ asset('images/logo.png') }}" alt="Company Logo">
    <h2>Bulan Transport Cooperative</h2>
    <p>Deduction Report</p>
    <p><strong>Date:</strong> {{ now()->format('F d, Y') }}</p>
</div>

<table>
    <thead>
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
        <tr>
            <td colspan="4" style="text-align: center;">No deductions found.</td>
        </tr>
    @endforelse
    </tbody>
</table>

<div class="signature-section">
    <p class="mb-5">Prepared by:</p>
    <div class="signature-line">Admin Signature</div>
</div>

</body>
</html>
