@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Deduction Records</h2>
            <p class="text-muted small mb-0">View and manage all employee deductions</p>
        </div>

        <div class="mt-3 mt-md-0">
            <button class="btn btn-custom-blue px-4 py-2" data-bs-toggle="modal" data-bs-target="#addDeductionModal">
                <i class="bi bi-plus-circle me-1"></i> Add Deduction
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.deductions.index') }}" class="card shadow-sm border-0 mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-4">
                <label for="date_range" class="form-label fw-semibold small">Date</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-date"></i></span>
                    <input type="text" id="date_range" class="form-control border-0 shadow-sm small" placeholder="Select date range" readonly>
                </div>
                <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
            </div>

            <div class="col-md-4">
                <label for="type" class="form-label fw-semibold small">Deduction Type</label>
                <select name="type" id="type" class="form-select form-select-sm shadow-sm border-0">
                    <option value="">All Types</option>
                    <option value="Bus Damage" {{ request('type') == 'Bus Damage' ? 'selected' : '' }}>Bus Damage</option>
                    <option value="Cash Advance" {{ request('type') == 'Cash Advance' ? 'selected' : '' }}>Cash Advance</option>
                    <option value="SSS" {{ request('type') == 'SSS' ? 'selected' : '' }}>SSS</option>
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm text-white shadow-sm px-3" style="background-color:#17007C; border:none;">
                    <i class="bi bi-funnel"></i> Apply
                </button>
                <a href="{{ route('admin.deductions.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm px-3">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Export & Actions --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.deductions.export.pdf', request()->query()) }}" class="btn btn-sm btn-outline-danger shadow-sm">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
        <a href="{{ route('admin.deductions.print', request()->query()) }}" class="btn btn-sm btn-outline-secondary shadow-sm" target="_blank">
            <i class="bi bi-printer"></i> Print
        </a>
    </div>

    {{-- Deduction Table --}}
    <div class="card shadow-sm">
        <div class="card-body table-responsive p-3">
            <table class="table table-hover deduction-table mb-0">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th class="actions-col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deductions as $index => $deduction)
                        <tr id="deduction-row-{{ $deduction->id }}">
                            <td>{{ ($deductions->currentPage() - 1) * $deductions->perPage() + $index + 1 }}</td>
                            <td>{{ $deduction->employee->first_name }} {{ $deduction->employee->last_name }}</td>
                            <td>{{ $deduction->type }}</td>
                            <td>₱{{ number_format($deduction->amount,2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($deduction->date)->format('M d, Y') }}</td>
                            <td class="actions-col">
                                <button class="btn btn-outline-primary btn-sm action-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editDeductionModal{{ $deduction->id }}" 
                                        title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                    <span class="action-text">Edit</span>
                                </button>

                                <button class="btn btn-outline-danger btn-sm action-btn" 
                                        onclick="confirmDeleteDeduction({{ $deduction->id }})" 
                                        title="Delete">
                                    <i class="bi bi-trash"></i>
                                    <span class="action-text">Delete</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Edit Deduction Modal -->
                        <div class="modal fade" id="editDeductionModal{{ $deduction->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width:600px;">
                                <div class="modal-content shadow-lg rounded-4 border-0">
                                    <form action="{{ route('admin.deductions.update',$deduction->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-header text-white rounded-top-4" style="background-color: #17007C;">
                                            <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Deduction</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Employee</label>
                                                <select name="employee_id" class="form-select" required>
                                                    @foreach($employees as $emp)
                                                        <option value="{{ $emp->id }}" {{ $emp->id == $deduction->employee_id ? 'selected':'' }}>
                                                            {{ $emp->first_name }} {{ $emp->last_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Type</label>
                                                <select name="type" class="form-select" required>
                                                    <option value="Bus Damage" {{ $deduction->type=='Bus Damage'?'selected':'' }}>Bus Damage</option>
                                                    <option value="Cash Advance" {{ $deduction->type=='Cash Advance'?'selected':'' }}>Cash Advance</option>
                                                    <option value="SSS" {{ $deduction->type=='SSS'?'selected':'' }}>SSS</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Amount</label>
                                                <input type="number" step="0.01" name="amount" class="form-control" value="{{ $deduction->amount }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Date</label>
                                                <input type="date" name="date" class="form-control" value="{{ $deduction->date }}" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update</button>
                                            <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No deduction records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if ($deductions instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="d-flex justify-content-between align-items-center p-3 flex-wrap">
                    <div class="small text-muted mb-2">
                        Showing {{ $deductions->firstItem() }} to {{ $deductions->lastItem() }} of {{ $deductions->total() }} results
                    </div>
                    <nav>
                        <ul class="pagination mb-0 pagination-lg gap-2">
                            <li class="page-item {{ $deductions->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link rounded-pill shadow-sm px-4" href="{{ $deductions->previousPageUrl() ?? '#' }}">Previous</a>
                            </li>
                            <li class="page-item {{ !$deductions->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link rounded-pill shadow-sm px-4" href="{{ $deductions->nextPageUrl() ?? '#' }}">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Deduction Modal -->
<div class="modal fade" id="addDeductionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content shadow-lg rounded-4 border-0">
            <form method="POST" action="{{ route('admin.deductions.store') }}">
                @csrf
                <div class="modal-header text-white rounded-top-4" style="background-color: #17007C;">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Deduction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->first_name }} {{ $emp->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="">-- Select Type --</option>
                            <option value="Bus Damage">Bus Damage</option>
                            <option value="Cash Advance">Cash Advance</option>
                            <option value="SSS">SSS</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-custom-blue"><i class="bi bi-plus-circle me-1"></i> Add & Save</button>
                    <button type="button" class="btn btn-outline-custom-blue rounded-3" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
.btn-custom-blue {
    background-color: #17007C; /* deep blue */
    color: #fff;
    border: none;
    border-radius: 0.5rem; /* rounded corners like employee button */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* subtle shadow */
    transition: background-color 0.3s, transform 0.2s;
}

.btn-custom-blue:hover,
.btn-custom-blue:focus,
.btn-custom-blue:active {
    background-color: #17007C; /* same color, no change on hover */
    color: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px); /* subtle lift effect */
}


.btn-outline-custom-blue {
    color: #17007C;
    border: 1px solid #17007C;
    border-radius: 0.5rem;
    padding: 0.5rem 1.2rem;
}
.btn-outline-custom-blue:hover { background-color: #17007C; color: #fff; }

.deduction-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}
.deduction-table thead th {
    font-weight: 600;
    background-color: #17007C !important;
    color: #fff !important;
    padding: 10px;
    text-align: left;
    white-space: nowrap;
    border: 1px solid #17007C !important;
}
.deduction-table tbody td {
    vertical-align: middle;
    text-align: left;
    padding: 8px 10px;
    border: 1px solid #dee2e6;
}
.deduction-table tbody tr:nth-child(even) { background-color: #f9f9ff; }
.deduction-table tbody tr:hover { background-color: #eef2ff; }

.actions-col { width: 180px; text-align: center; }
.actions-col .action-btn { margin: 2px 0; white-space: nowrap; }
.actions-col .action-btn .action-text { display: none; margin-left: 4px; }
.actions-col .action-btn:hover .action-text { display: inline; }

/* Pagination */
.page-link { border-radius: 20px; padding: 0.25rem 0.75rem; font-size: 0.875rem; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
.page-link:hover { background-color: #17007C; color: white; }
</style>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- ✅ Auto-close SweetAlert success message --}}
@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: "{{ session('success') }}",
    confirmButtonText: 'OK',
    timer: 5000,
    timerProgressBar: true,
    allowOutsideClick: false,
    allowEscapeKey: false
});
</script>
@endif
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

document.addEventListener("DOMContentLoaded", function () {
    const picker = new Litepicker({
        element: document.getElementById('date_range'),
        singleMode: false,
        format: 'YYYY-MM-DD',
        autoApply: true,
        showWeekNumbers: true,
        showOnFocus: true,
        setup: (picker) => {
            picker.on('selected', (date1, date2) => {
                document.getElementById('start_date').value = date1.format('YYYY-MM-DD');
                document.getElementById('end_date').value = date2.format('YYYY-MM-DD');
                document.getElementById('date_range').value =
                    date1.format('MMM DD, YYYY') + ' - ' + date2.format('MMM DD, YYYY');
            });
        }
    });

    const start = '{{ $startDate }}';
    const end = '{{ $endDate }}';
    picker.setDateRange(start, end);

    document.getElementById('date_range').value =
        '{{ \Carbon\Carbon::parse($startDate)->format("M d, Y") }} - {{ \Carbon\Carbon::parse($endDate)->format("M d, Y") }}';
});

// SweetAlert2 delete function
function confirmDeleteDeduction(deductionId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This deduction will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const token = "{{ csrf_token() }}";
            fetch(`/admin/deductions/${deductionId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(response => {
                if (response.ok) {
                    const row = document.getElementById(`deduction-row-${deductionId}`);
                    if(row) row.remove();

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Deduction has been deleted.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', 'Something went wrong!', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Something went wrong!', 'error');
            });
        }
    });
}
</script>

@endpush
