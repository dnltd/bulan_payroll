@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Deduction List</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Filter -->
    <form method="GET" class="row mb-3">
        <div class="col-md-4">
            <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="Bus Damage" {{ request('type') == 'Bus Damage' ? 'selected' : '' }}>Bus Damage</option>
                <option value="Cash Advance" {{ request('type') == 'Cash Advance' ? 'selected' : '' }}>Cash Advance</option>
                <option value="SSS" {{ request('type') == 'SSS' ? 'selected' : '' }}>SSS</option>
            </select>
        </div>
        <div class="col-md-4">
            <select name="employee_id" class="form-select">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->full_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.deductions.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Deduction Records</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.deductions.export.pdf') }}" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
                <a href="{{ route('admin.deductions.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-printer"></i> Print
</a>

                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Add Deduction
                </button>
            </div>
        </div>
        <div class="card shadow-sm">
        <div class="card-body p-3 table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deductions as $deduction)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $deduction->employee->full_name }}</td>
                            <td>{{ $deduction->type }}</td>
                            <td>₱{{ number_format($deduction->amount, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($deduction->date)->toFormattedDateString() }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.deductions.destroy', $deduction->id) }}" onsubmit="return confirm('Delete this deduction?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No deductions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $deductions->links() }}
        </div>
    </div>
</div>

<!-- Printable Section -->
<div id="printArea" style="display:none;">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo.png') }}" alt="Company Logo" style="width: 80px;">
        <h4 class="mt-2 mb-0">Bulan Transport Cooperative</h4>
        <p class="mb-0">Deduction Report</p>
        <p><strong>Date:</strong> {{ now()->format('F d, Y') }}</p>
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
            @foreach($deductions as $deduction)
                <tr>
                    <td>{{ $deduction->employee->full_name }}</td>
                    <td>{{ $deduction->type }}</td>
                    <td>₱{{ number_format($deduction->amount, 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($deduction->date)->toFormattedDateString() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="text-center mt-5">
        <p class="mb-5">Prepared by:</p>
        <div style="margin: auto; width: 200px; border-top: 1px solid #000; padding-top: 5px;">
            Admin Signature
        </div>
    </div>
</div>

<!-- Add Deduction Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.deductions.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Deduction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Type</label>
                        <select name="type" class="form-select" required>
                            <option value="">-- Select Type --</option>
                            <option value="Bus Damage">Bus Damage</option>
                            <option value="Cash Advance">Cash Advance</option>
                            <option value="SSS">SSS</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Amount (₱)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" required value="{{ now()->toDateString() }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Add</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
function printReport() {
    const printContent = document.getElementById('printArea').innerHTML;

    if (!printContent) {
        alert('Nothing to print. Content not found.');
        return;
    }

    const printWindow = window.open('', '_blank', 'width=1000,height=800');
    if (!printWindow) {
        alert('Popup blocked! Please allow popups for this site.');
        return;
    }

    printWindow.document.open();
    printWindow.document.write(`
        <html>
        <head>
            <title>Print Deduction Report</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            <style>
                body { font-family: Arial, sans-serif; padding: 30px; }
                .text-center { text-align: center; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #000; padding: 8px; }
                .mt-5 { margin-top: 3rem; }
                .mb-5 { margin-bottom: 3rem; }
                img { max-width: 100px; margin-bottom: 10px; }
            </style>
        </head>
        <body>
            ${printContent}
            <script>
                window.onload = function() {
                    window.print();
                    setTimeout(() => window.close(), 1000);
                }
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
}
</script>
@endsection
