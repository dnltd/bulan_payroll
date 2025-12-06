@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Main Title --}}
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Payroll Records</h4>
        <p class="text-muted small mb-0">View and manage payroll details for all employees</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Summary Cards --}}
    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-white h-100" style="background: linear-gradient(135deg, #17007C, #3422b5);">
                <div class="card-body position-relative">
                    <i class="bi bi-cash-coin fs-3 position-absolute top-0 end-0 m-3 opacity-75"></i>
                    <h6 class="text-uppercase small mb-1">Total Payroll</h6>
                    <h4 class="fw-bold">₱{{ number_format($totalPayroll, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-white h-100" style="background: linear-gradient(135deg, #17007C, #3422b5);">
                <div class="card-body position-relative">
                    <i class="bi bi-dash-circle fs-3 position-absolute top-0 end-0 m-3 opacity-75"></i>
                    <h6 class="text-uppercase small mb-1">Total Deductions</h6>
                    <h4 class="fw-bold">₱{{ number_format($totalDeductions, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-white h-100" style="background: linear-gradient(135deg, #17007C, #3422b5);">
                <div class="card-body position-relative">
                    <i class="bi bi-wallet2 fs-3 position-absolute top-0 end-0 m-3 opacity-75"></i>
                    <h6 class="text-uppercase small mb-1">Total Net</h6>
                    <h4 class="fw-bold">₱{{ number_format($totalNet, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form id="filterForm" method="GET" action="{{ route('admin.payroll.index') }}" class="card shadow-sm border-0 mb-4">
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-4">
                <label for="date_range" class="form-label fw-semibold small">Payroll Week (Sat–Fri)</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-date"></i></span>
                    <input type="text" id="date_range" class="form-control border-0 shadow-sm small" placeholder="Select week" readonly>
                </div>
                <input type="hidden" name="start_date" id="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" id="end_date" value="{{ $endDate }}">
            </div>

            <div class="col-md-4">
                <label for="position" class="form-label fw-semibold small">Position</label>
                <select name="position" id="position" class="form-select form-select-sm shadow-sm border-0">
                    <option value="">All Positions</option>
                    @foreach(\App\Models\Employee::select('position')->distinct()->pluck('position') as $position)
                        <option value="{{ $position }}" {{ request('position') == $position ? 'selected' : '' }}>
                            {{ $position }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm text-white shadow-sm px-3" style="background-color:#17007C; border:none;">
                    <i class="bi bi-funnel"></i> Apply
                </button>
                <a href="{{ route('admin.payroll.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm px-3">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Export & Actions --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <form action="{{ route('admin.payslips.bulk.print') }}" method="GET" target="_blank" class="d-inline">
            <input type="hidden" name="start_date" id="bulk_start_date" value="{{ request('start_date') }}">
            <input type="hidden" name="end_date" id="bulk_end_date" value="{{ request('end_date') }}">
            <button type="submit" class="btn btn-sm text-white shadow-sm" style="background-color:#17007C; border:none;">
                <i class="bi bi-printer"></i> Print All Payslips
            </button>
        </form>

        <a href="{{ route('admin.payroll.export.pdf', request()->query()) }}" class="btn btn-sm btn-outline-danger shadow-sm">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>

        <a href="{{ route('admin.payroll.print', request()->query()) }}" class="btn btn-sm btn-outline-secondary shadow-sm" target="_blank">
            <i class="bi bi-printer"></i> Print Payroll
        </a>
    </div>

    {{-- Payroll Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-3 table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white payroll-table mb-0">
                <thead style="background-color:#17007C; color:white;">
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $index => $payroll)
                        @php
                            $employee = $payroll->employee;
                            $rate = $employee->salaryRate->daily_rate ?? 0;
                            $noOfDays = $rate > 0
                                ? floor(($payroll->gross_salary - ($payroll->overtime_pay ?? 0) - ($payroll->holiday_pay ?? 0)) / $rate)
                                : 0;
                            $overtime = $payroll->overtime_pay ?? 0;
                            $holidayPay = $payroll->holiday_pay ?? 0;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $employee->full_name }}</td>
                            <td>{{ $noOfDays }}</td>
                            <td>₱{{ number_format($rate, 2) }}</td>
                            <td>₱{{ number_format($overtime, 2) }}</td>
                            <td>₱{{ number_format($holidayPay, 2) }}</td>
                            <td>₱{{ number_format($payroll->gross_salary, 2) }}</td>
                            <td>₱{{ number_format($payroll->deductions, 2) }}</td>
                            <td>₱{{ number_format($payroll->net_salary, 2) }}</td>
                            <td>
                                <a href="{{ route('admin.payslips.download', $payroll->id) }}"
                                   class="btn btn-outline-danger btn-sm action-btn" title="Download PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                    <span class="action-text">PDF</span>
                                </a>
                                <a href="{{ route('admin.payslips.print', $payroll->id) }}"
                                   target="_blank" class="btn btn-outline-secondary btn-sm action-btn" title="Print">
                                    <i class="bi bi-printer"></i>
                                    <span class="action-text">Print</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">No payroll data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center p-3 flex-wrap">
                <div class="small text-muted mb-2">
                    Showing {{ $payrolls->firstItem() }} to {{ $payrolls->lastItem() }} of {{ $payrolls->total() }} results
                </div>
                <nav>
                    <ul class="pagination mb-0 pagination-lg gap-2">
                        <li class="page-item {{ $payrolls->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-pill shadow-sm px-4"
                               href="{{ $payrolls->previousPageUrl() ?? '#' }}">Previous</a>
                        </li>
                        <li class="page-item {{ !$payrolls->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-pill shadow-sm px-4"
                               href="{{ $payrolls->nextPageUrl() ?? '#' }}">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

{{-- Styles --}}
<style>
/* Payroll Table */
.payroll-table {
    width: 100%;
    font-size: 0.85rem;
    border-collapse: collapse;
    table-layout: auto; /* prevents overflow */
}

/* Table Header */
.payroll-table thead th {
    background-color: #17007C !important;
    color: #fff !important;
    font-weight: 600;
    text-align: left;
    padding: 10px;
    border: 1px solid #17007C !important;
    white-space: nowrap; /* prevent wrapping */
}

/* Table Body Cells */
.payroll-table td {
    padding: 8px 10px;
    text-align: left; /* ✅ left-align body text */
    vertical-align: middle;
    border: 1px solid #dee2e6;
}

/* Alternate Row Striping */
.payroll-table tbody tr:nth-child(even) {
    background-color: #f9f9ff; /* soft blue tint */
}

/* Hover Effect */
.payroll-table tbody tr:hover {
    background-color: #eef2ff;
}

/* Responsive Fix */
.payroll-table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}


.actions-col { width: 220px; text-align:center; }
.actions-col .action-btn { margin:2px 0; white-space:nowrap; }
.action-btn .action-text { display:none; margin-left:4px; }
.action-btn:hover .action-text { display:inline; }

.page-link {
    border-radius: 20px;
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: all 0.2s ease-in-out;
}
.page-link:hover {
    background-color: #007bff;
    color: white;
}
</style>
@endsection

{{-- JS --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    const dateRange = document.getElementById('date_range');
    const form = document.getElementById('filterForm'); // ✅ Make sure your form has id="filterForm"

    // Initialize Litepicker
    const picker = new Litepicker({
        element: dateRange,
        singleMode: false,
        format: 'YYYY-MM-DD',
        autoApply: true,
        showWeekNumbers: true,
        showOnFocus: true,
        setup: (picker) => {
            picker.on('selected', (date1, date2) => {
                startInput.value = date1.format('YYYY-MM-DD');
                endInput.value = date2.format('YYYY-MM-DD');
                dateRange.value = date1.format('MMM DD, YYYY') + ' - ' + date2.format('MMM DD, YYYY');
            });
        }
    });

    // ✅ Set initial range from Laravel Blade
    picker.setDateRange('{{ $startDate }}', '{{ $endDate }}');
    dateRange.value =
        '{{ \Carbon\Carbon::parse($startDate)->format("M d, Y") }} - {{ \Carbon\Carbon::parse($endDate)->format("M d, Y") }}';

    // ✅ Auto-apply filter on first load if no query params present
    const urlParams = new URLSearchParams(window.location.search);
    const hasFilters = urlParams.has('start_date') || urlParams.has('end_date') ||
                       urlParams.has('position') || urlParams.has('employee_id') || urlParams.has('search');

    if (!hasFilters) {
        const today = new Date();
        const lastSaturday = new Date(today);
        lastSaturday.setDate(today.getDate() - ((today.getDay() + 1) % 7) - 7);
        const nextFriday = new Date(lastSaturday);
        nextFriday.setDate(lastSaturday.getDate() + 6);

        const formatDate = d => d.toISOString().split('T')[0];

        startInput.value = formatDate(lastSaturday);
        endInput.value = formatDate(nextFriday);

        form.submit();
    }
});
</script>

@endpush
