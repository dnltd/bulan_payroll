<!DOCTYPE html>
<html>
<head>
    <title>Employee Report</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h3 style="text-align:center;">Bulan Transport Cooperative</h3>
    <h4 style="text-align:center;">Employee Report</h4>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Salary Rate</th>
                <th>Contact</th>
                <th>Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $e)
            <tr>
                <td>{{ $e->full_name }}</td>
                <td>{{ $e->position }}</td>
                <td>₱{{ number_format($e->salaryRate->daily_rate ?? 0, 2) }}</td>
                <td>{{ $e->contact_number }}</td>
                <td>{{ $e->address }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
