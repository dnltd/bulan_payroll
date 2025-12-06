<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Report</title>
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

<script>
    window.onload = function () {
        window.print();
        setTimeout(() => window.close(), 800);
    };
</script>

<body>

<div class="report-header">
    <img src="{{ asset('images/logo.png') }}" alt="Company Logo">
    <h2>Bulan Transport Cooperative</h2>
    <p>Zone 6 Bulan, Sorsogon</p>
    <p>Email: b.bultransco@yahoo.com | Contact: 0964-170-9298</p>
    <p>
        <strong>SALARY FROM: {{ \Carbon\Carbon::parse(request('start_date'))->format('F d, Y') }} 
        - {{ \Carbon\Carbon::parse(request('end_date'))->format('F d, Y') }}</strong>
    </p>
    @if(request('position'))
        <p><strong>Position:</strong> {{ request('position') }}</p>
    @else
        <p><strong>Position:</strong> All</p>
    @endif
</div>

<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Name</th>
            <th>No. of Days</th>
            <th>Rate per Day</th>
            <th>Overtime</th>
            <th>Holiday Pay</th>
            <th>Gross Salary</th>
            <th>Deductions</th>
            <th>Net Salary</th>
            <th>Signature</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payrolls as $index => $payroll)
            @php
                $employee = $payroll->employee;
                $rate = $employee->salaryRate->daily_rate ?? 0;

                $workDays = $payroll->work_days ?? 0;
                $holidayPay = $payroll->holiday_pay ?? 0;
                $overtimePay = $payroll->overtime_pay ?? 0;
                $gross = $payroll->gross_salary ?? 0;
                $deductions = $payroll->deductions ?? 0;
                $net = $payroll->net_salary ?? 0;

                $otUnits = $payroll->overtime_units ?? 0;
                if (in_array($employee->position, ['Driver', 'Conductor'])) {
                    $otUnits .= ' round(s)';
                } else {
                    $otUnits .= ' hour(s)';
                }
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $employee->full_name }}</td>
                <td>{{ $workDays }}</td>
                <td>₱{{ number_format($rate, 2) }}</td>
                <td>{{ $otUnits }}<br>₱{{ number_format($overtimePay, 2) }}</td>
                <td>₱{{ number_format($holidayPay, 2) }}</td>
                <td>₱{{ number_format($gross, 2) }}</td>
                <td>₱{{ number_format($deductions, 2) }}</td>
                <td>₱{{ number_format($net, 2) }}</td>
                <td></td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="signature-section">
    <div class="signature-line">Prepared by: {{ auth()->user()->full_name }}</div>
</div>

</body>
</html>
