<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip</title>
    <style>
        @page {
            margin: 30px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        .container {
            width: 100%;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            width: 80px;
            height: auto;
        }
        .salary, .details {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .salary th, .salary td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        .signature {
            margin-top: 50px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ public_path('images/logo.png') }}" alt="Company Logo">
            <h2>Bulan Transport Cooperative</h2>
            <p>Automated Payroll System Payslip</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($payroll->date)->format('F d, Y') }}</p>
        </div>

        <div class="details">
            <p><strong>Employee Name:</strong> {{ $payroll->employee->full_name }}</p>
            <p><strong>Position:</strong> {{ $payroll->employee->position }}</p>
        </div>

        <table class="salary">
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
</body>
</html>
