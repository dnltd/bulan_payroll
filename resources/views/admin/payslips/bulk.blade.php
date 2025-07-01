<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulk Payslips</title>
    <style>
        @page {
            margin: 30px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 0;
        }
        .payslip {
            page-break-after: always;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header img {
            width: 70px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }
        .signature {
            margin-top: 40px;
            text-align: right;
        }
    </style>
</head>
<body>
    @foreach ($payrolls as $payroll)
        <div class="payslip">
            <div class="header">
                <img src="{{ public_path('images/logo.png') }}" alt="Logo">
                <h3>Bulan Transport Cooperative</h3>
                <p>Automated Payroll Payslip</p>
                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($payroll->date)->format('F d, Y') }}</p>
            </div>

            <p><strong>Employee Name:</strong> {{ $payroll->employee->full_name }}</p>
            <p><strong>Position:</strong> {{ $payroll->employee->position }}</p>

            <table>
                <tr>
                    <th>Description</th>
                    <th>Amount (₱)</th>
                </tr>
                <tr>
                    <td>Gross Salary</td>
                    <td>{{ number_format($payroll->gross_salary, 2) }}</td>
                </tr>
                <tr>
                    <td>Deductions</td>
                    <td>{{ number_format($payroll->deductions, 2) }}</td>
                </tr>
                <tr>
                    <th>Net Salary</th>
                    <th>{{ number_format($payroll->net_salary, 2) }}</th>
                </tr>
            </table>

            <div class="signature">
                <p>Prepared by:</p>
                <p>___________________________</p>
                <p>Admin / Payroll Officer</p>
            </div>
        </div>
    @endforeach
</body>
</html>
