<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Payslips</title>
    <style>
    @page { size: letter portrait; margin: 10px; }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 10px;
        margin: 5px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-content: flex-start;
    }

    .payslip {
        width: 48%;
        height: 55%;
        border: 1.5px solid #000;
        margin-bottom: 15px;
        padding: 8px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        page-break-inside: avoid;
    }

    .header {
        text-align: center;
        margin-bottom: 8px;
    }

    .header img {
        width: 45px;
        height: auto;
        margin-bottom: 5px;
    }

    .header h3 { margin: 3px 0; font-size: 10px; font-weight: bold; }
    .header p { margin: 3px; font-size: 10px; }

    /* ✅ Make all tables attached */
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        border: 1px solid #000;
    }

    /* ✅ Remove spacing between consecutive tables */
    table + table {
        margin-top: -1px; /* overlap border */
    }

    th, td {
        border: 1px solid #000;
        padding: 3px 4px;
        font-size: 10px;
    }

    .blank-row td {
        padding: 6px 4px;
        border: 1px solid #000;
    }

    th { text-align: left; width: 45%; background-color: #f2f2f2; }
    td { text-align: center; }

    .earnings-deductions {
        display: flex;
        justify-content: space-between;
        gap: 0;
        margin-top: -1px; /* ✅ attached to previous table */
    }

    .earnings-deductions table {
        width: 50%;
        border-collapse: collapse;
        margin: 0;
    }

    .earnings-deductions table + table {
        margin-top: 0;
        border-left: none; /* ✅ avoid double middle border */
    }

    .section-title {
        background-color: #e0e0e0;
        text-align: center;
        font-weight: bold;
        font-size: 10px;
    }

    .net-salary {
        border: 1.5px solid #000;
        font-weight: bold;
        font-size: 10px;
        text-align: center;
        padding: 3px;
        background-color: #f9f9f9;
    }

    .signature {
        text-align: right;
        font-size: 10px;
        margin-top: 25px;
    }

    @media print {
        body { margin: 0; }
    }
</style>

</head>
<body>

    <div class="payslip">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="Logo"><br>
            <h3>BULAN TRANSPORT COOPERATIVE</h3>
            <p>Bulan, Sorsogon</p>
            <p><strong>Payslip for the Week of</strong><br>
                {{ \Carbon\Carbon::parse($payroll->start_date)->format('F d, Y') }} -
                {{ \Carbon\Carbon::parse($payroll->end_date)->format('F d, Y') }}
            </p>
        </div>

        <!-- Employee Info -->
        <table>
            <tr><th>Employee Name:</th><td>{{ $payroll->employee->full_name }}</td></tr>
            <tr><th>Designation:</th><td>{{ $payroll->employee->position }}</td></tr>
            <tr><th>Rate Per Day:</th><td>₱{{ number_format($payroll->employee->salaryRate->daily_rate ?? 0, 2) }}</td></tr>
            <tr><th>OT Pay:</th><td>₱{{ number_format($payroll->overtime_pay, 2) }}</td></tr>
            <tr><th>Holiday Pay:</th><td>₱{{ number_format($payroll->holiday_pay, 2) }}</td></tr>
        </table>

        <!-- Work Summary -->
        <table>
            <tr><th>Work Days:</th><td>{{ $payroll->work_days }}</td></tr>
            <tr>
                <th>OT:</th>
                <td>
                    @if(in_array($payroll->employee->position, ['Driver', 'Conductor']))
                        {{ $payroll->overtime_units }} round(s)
                    @else
                        {{ $payroll->overtime_units }} hour(s)
                    @endif
                </td>
            </tr>
            <tr>
                <th>Holiday:</th>
                <td>
                    @if(!empty($payroll->holiday_names))
                        {{ count($payroll->holiday_names) }} day(s)
                    @else
                        0 day(s)
                    @endif
                </td>
            </tr>
        </table>

        <!-- Earnings & Deductions -->
        <div class="earnings-deductions">
            <table>
                <tr><th colspan="2" class="section-title">Earnings</th></tr>
                <tr><td>Basic Pay</td><td>₱{{ number_format($payroll->gross_salary - $payroll->overtime_pay - $payroll->holiday_pay, 2) }}</td></tr>
                <tr><td>Overtime</td><td>₱{{ number_format($payroll->overtime_pay, 2) }}</td></tr>
                <tr><td>Holiday</td><td>₱{{ number_format($payroll->holiday_pay, 2) }}</td></tr>
                <tr class="blank-row"><td></td><td></td></tr>
                <tr class="blank-row"><td></td><td></td></tr>
                <tr><th>Total</th><th>₱{{ number_format($payroll->gross_salary, 2) }}</th></tr>
            </table>

            <table>
    <tr><th colspan="2" class="section-title">Deductions</th></tr>

    @php
        $deductionData = $payroll->deductions_list;
        if (is_string($deductionData)) {
            $deductionData = json_decode($deductionData, true) ?? [];
        }

        $deductions = collect($deductionData);

        // Use case-insensitive partial matching to catch different variations
        $sss = $deductions->filter(fn($d) => stripos($d['type'], 'SSS') !== false)->sum('amount');
        $ca  = $deductions->filter(fn($d) => stripos($d['type'], 'Cash Advance') !== false || $d['type'] === 'CA')->sum('amount');
        $busDamage = $deductions->filter(fn($d) => stripos($d['type'], 'Bus Damage') !== false)->sum('amount');

        // Fix: Match ANY variation of "carry over previous" or "carry-over previous"
        $carryPrev = $deductions->filter(fn($d) =>
            preg_match('/carry[\s-]*over.*(prev|previous)/i', $d['type'])
        )->sum('amount');

        // Match any "carry over next" variant
        $carryNext = $deductions->filter(fn($d) =>
            preg_match('/carry[\s-]*over.*(next|to next)/i', $d['type'])
        )->sum('amount');

        // Detect if there are other deduction types
        $knownTypes = ['SSS', 'CA', 'Cash Advance', 'Bus Damage', 'Carry-Over (Previous)', 'Carry-Over (To Next Payroll)'];
        $hasOthers = $deductions->filter(function($d) use ($knownTypes) {
            return !collect($knownTypes)->contains(fn($type) => stripos($d['type'], $type) !== false);
        })->isNotEmpty();

        // Only include deductions that affect this payroll (exclude carry-over next)
        $totalDeductions = $sss + $ca + $busDamage + $carryPrev;
    @endphp

    {{-- SSS --}}
    @if($sss > 0)
        <tr><td>SSS</td><td>₱{{ number_format($sss, 2) }}</td></tr>
    @endif

    {{-- Others (always visible even if empty) --}}
    <tr><td>Others</td><td></td></tr>

    {{-- Cash Advance --}}
    @if($ca > 0)
        <tr><td>Cash Advance</td><td>₱{{ number_format($ca, 2) }}</td></tr>
    @endif

    {{-- Bus Damage (only if exists) --}}
    @if($busDamage > 0)
        <tr><td>Bus Damage</td><td>₱{{ number_format($busDamage, 2) }}</td></tr>
    @endif

    {{-- Carry Over (Previous) --}}
    @if($carryPrev > 0)
        <tr><td>Carry Over (Previous)</td><td>₱{{ number_format($carryPrev, 2) }}</td></tr>
    @endif

    {{-- Carry Over (To Next Payroll) --}}
    @if($carryNext > 0)
        <tr><td>Carry Over (To Next Payroll)</td><td>₱{{ number_format($carryNext, 2) }}</td></tr>
    @endif

    {{-- Blank spacing --}}
    <tr class="blank-row"><td></td><td></td></tr>
    <tr class="blank-row"><td></td><td></td></tr>
    <tr class="blank-row"><td></td><td></td></tr>
    {{-- Total --}}
    <tr>
        <th>Total</th>
        <th>₱{{ number_format($totalDeductions, 2) }}</th>
    </tr>
</table>




        </div>

        <div class="net-salary">
            Net Salary: ₱{{ number_format($payroll->net_salary, 2) }}
        </div>

        <div class="signature">
            Prepared by: <strong>{{ auth()->user()->full_name }}</strong><br>
            Admin / Payroll Officer
        </div>
    </div>


<script>
    window.onload = () => window.print();
    window.onafterprint = () => {
        window.location.href = "{{ route('admin.payroll.index') }}";
    };
</script>
</body>
</html>
