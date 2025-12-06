@extends('layouts.app')

@section('content')
<div class="container py-4">


    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Attendance Records</h2>
            <p class="text-muted small mb-0">View and manage all employee attendance records</p>
        </div>

        <div class="mt-3 mt-md-0 d-flex gap-2">
    <a href="{{ route('admin.attendance.scan') }}" class="btn btn-custom-blue px-4 py-2">
        <i class="bi bi-camera me-1"></i> Record Attendance via Face Scan
    </a>
</div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.attendance.index') }}" class="card shadow-sm border-0 mb-4">
        <div class="card-body row g-3 align-items-end">

            {{-- Position Filter --}}
            <div class="col-md-4">
                <label for="position" class="form-label fw-semibold small">Position</label>
                <select name="position" id="position" class="form-select form-select-sm shadow-sm border-0">
                    <option value="">All Positions</option>
                    @foreach(['Driver','Conductor','Secretary','Dispatcher','Inspector','Treasurer','General Manager'] as $position)
                        <option value="{{ $position }}" {{ request('position') == $position ? 'selected' : '' }}>{{ $position }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Date Filter --}}
            <div class="col-md-4">
                <label for="date" class="form-label fw-semibold small">Date</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-date"></i></span>
                    <input type="date" name="date" id="date" class="form-control border-0 shadow-sm small"
                        value="{{ request('date', \Carbon\Carbon::today()->format('Y-m-d')) }}">
                </div>
            </div>

            {{-- Buttons --}}
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm text-white shadow-sm px-3" style="background-color:#17007C; border:none;">
                    <i class="bi bi-funnel"></i> Apply
                </button>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm px-3">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>

        </div>
    </form>

    {{-- Attendance Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-3 table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white deduction-table mb-0">
                <thead style="background-color:#17007C; color:white;">
    <tr>
        <th>No.</th>
        <th>Name</th>
        <th>Position</th>
        <th>Date</th>
        <th>Time In</th>
        <th>Time Out</th>
        <th>Rounds</th>
        <th>Holiday</th>
        <th>Action</th> <!-- new -->
    </tr>
</thead>
<tbody>
    @forelse($attendances as $index => $att)
        @php
            $datePH = \Carbon\Carbon::parse($att->date)
                        ->timezone('Asia/Manila')
                        ->format('M d, Y');
            $timeInPH = $att->time_in
                        ? \Carbon\Carbon::createFromFormat('H:i:s', $att->time_in, 'Asia/Manila')
                                ->format('h:i A')
                        : '-';
            $timeOutPH = $att->time_out
                        ? \Carbon\Carbon::createFromFormat('H:i:s', $att->time_out, 'Asia/Manila')
                                ->format('h:i A')
                        : '-';
        @endphp
        <tr data-employee-id="{{ $att->employee->id }}">
            <td>{{ ($attendances->currentPage() - 1) * $attendances->perPage() + $index + 1 }}</td>
            <td>{{ $att->employee->first_name }} {{ $att->employee->middle_name ?? '' }} {{ $att->employee->last_name }}</td>
            <td>{{ $att->employee->position ?? 'N/A' }}</td>
            <td>{{ $datePH }}</td>
            <td>{{ $timeInPH }}</td>
            <td>{{ $timeOutPH }}</td>
            <td>{{ $att->rounds ?? 'N/A' }}</td>
            <td>{{ $att->holiday->name ?? 'None' }}</td>
            <td>
    <button class="btn btn-sm btn-custom-blue end-shift-btn" 
    {{ $att->time_out ? 'disabled' : '' }}
    title="End Shift">
    <i class="bi bi-clock-fill"></i>
    <span class="action-text">End Shift</span>
</button>

</td>


        </tr>
    @empty
        <tr><td colspan="9" class="text-center text-muted">No attendance records found.</td></tr>
    @endforelse
</tbody>

            </table>

            {{-- Pagination --}}
            @if ($attendances instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="d-flex justify-content-between align-items-center p-3 flex-wrap">
                    <div class="small text-muted mb-2">
                        Showing {{ $attendances->firstItem() }} to {{ $attendances->lastItem() }} of {{ $attendances->total() }} results
                    </div>
                    <nav>
                        <ul class="pagination mb-0 pagination-lg gap-2">
                            <li class="page-item {{ $attendances->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link rounded-pill shadow-sm px-4" href="{{ $attendances->previousPageUrl() ?? '#' }}">Previous</a>
                            </li>
                            <li class="page-item {{ !$attendances->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link rounded-pill shadow-sm px-4" href="{{ $attendances->nextPageUrl() ?? '#' }}">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            @endif
        </div>
    </div>
</div>




{{-- Styles --}}
<style>

/* Icon-only by default, show text on hover */
.end-shift-btn .action-text {
    display: none;
}
.end-shift-btn:hover .action-text {
    display: inline;
}

/* Hover effect */
.end-shift-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

/* Disabled state */
.end-shift-btn:disabled {
    background-color: #aaa;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.btn-custom-blue {
    background-color: #17007C;
    color: #fff;
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: background-color 0.3s, transform 0.2s;
}
.btn-custom-blue:hover,
.btn-custom-blue:focus,
.btn-custom-blue:active {
    background-color: #17007C;
    color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    transform: translateY(-1px);
}

/* Attendance Table */
.deduction-table {
    width: 100%;
    font-size: 0.85rem;
    border-collapse: collapse;
}
.deduction-table thead th {
    background-color: #17007C !important;
    color: #fff !important;
    font-weight: 600;
    text-align: left;
    padding: 10px;
    border: 1px solid #17007C !important;
    white-space: nowrap;
}
.deduction-table tbody td {
    text-align: left;
    vertical-align: middle;
    padding: 8px 10px;
    border: 1px solid #dee2e6;
}
.deduction-table tbody tr:hover {
    background-color: #eef2ff;
}

.page-link {
    border-radius: 20px;
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: all 0.2s ease-in-out;
}
.page-link:hover {
    background-color: #17007C;
    color: white;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
// 🕓 Auto-disable "End Shift" if conditions not met
document.addEventListener('DOMContentLoaded', async function() {
    const rows = document.querySelectorAll('tr[data-employee-id]');

    for (const row of rows) {
        const employeeId = row.dataset.employeeId;
        const btn = row.querySelector('.end-shift-btn');
        if (!btn) continue;

        try {
            const response = await fetch(`{{ url('/attendance/status') }}/${employeeId}`);
            const data = await response.json();

            // Save reason for later use
            btn.dataset.reason = data.reason || '';

            if (data.can_end_shift) {
                btn.disabled = false;
                btn.title = "Click to end shift";
            } else {
                btn.disabled = true;
                btn.title = data.reason || "Cannot end shift yet";
            }
        } catch (err) {
            console.error('Error checking shift status:', err);
        }
    }
});


// 🟩 Handle "End Shift" button click
document.addEventListener('click', async function(e) {
    const btn = e.target.closest('.end-shift-btn'); // ✅ Works even if <i> is clicked
    if (!btn) return;

    const row = btn.closest('tr');
    const employeeId = row.dataset.employeeId;
    if (!employeeId) return;

    // 🔒 If disabled, show reason alert
    if (btn.disabled) {
        const reason = btn.dataset.reason || "Shift cannot be ended yet.";
        Swal.fire({
            title: 'Cannot End Shift',
            text: reason,
            icon: 'info',
            confirmButtonColor: '#17007C'
        });
        return;
    }

    // 🟢 Ask for confirmation
    const confirmResult = await Swal.fire({
        title: 'End Shift?',
        text: "This will record the Time Out for this employee.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, end shift'
    });
    if (!confirmResult.isConfirmed) return;

    // 🇵🇭 Get current Philippine time
    const now = new Date();
    const phTime = new Date(now.toLocaleString("en-US", { timeZone: "Asia/Manila" }));
    const hours = phTime.getHours().toString().padStart(2, '0');
    const minutes = phTime.getMinutes().toString().padStart(2, '0');
    const timeOut = `${hours}:${minutes}`;

    try {
        const response = await fetch("{{ route('admin.attendance.endShift') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ employee_id: employeeId, time_out: timeOut })
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire('Shift Ended', data.message, 'success').then(() => {
                location.reload(); // refresh table
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }

    } catch (err) {
        console.error(err);
        Swal.fire('Error', 'Could not end shift', 'error');
    }
});
</script>


@endsection
