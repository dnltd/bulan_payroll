<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dispatcher Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body {
    background-color: #f0f2f5;
    font-family: 'Segoe UI', sans-serif;
}
.logo { max-height: 40px; }

.btn {
    border-radius: 0.5rem;
    transition: all 0.2s;
}
.btn-primary {
    background-color: #17007C;
    border: none;
}
.btn-primary:hover, .btn-primary:focus {
    background-color: #3422b5;
}
.btn-outline-primary {
    color: #17007C;
    border-color: #17007C;
}
.btn-outline-primary:hover {
    background-color: #17007C;
    color: #fff;
}

.card {
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

table {
    font-size: 0.875rem;
}
.table th {
    background-color: #17007C !important;
    color: #fff !important;
}
.table-hover tbody tr:hover {
    background-color: #eef2ff;
}

.settings-btn {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
}

/* Small modal for mobile */
@media (max-width: 576px) {
    .modal-sm-custom { max-width: 90%; }
    .modal-content { font-size: 0.85rem; }
    .modal-footer .btn { font-size: 0.75rem; padding: 0.25rem 0.5rem; }
}
</style>
</head>
<body>

<div class="container py-3">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
            <h6 class="fw-bold mb-0 text-custom-blue">Dispatcher Dashboard</h6>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Settings -->
            <button class="settings-btn" data-bs-toggle="modal" data-bs-target="#changePasswordModal" title="Change Password">
                <img src="{{ asset('images/icons8-settings.svg') }}" alt="Settings" width="30" height="30">
            </button>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Scan Button -->
    <div class="mb-3">
        <a href="{{ route('dispatcher.scan.face') }}" class="btn btn-outline-primary w-100 shadow-sm">
            <i class="bi bi-camera"></i> Scan Face of Drivers and Conductors
        </a>
    </div>

   <!-- Filters -->
<div class="card shadow-sm mb-3 border-0 rounded-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 px-3">
        <span class="fw-semibold text-custom-blue" style="color:#17007C;">Filter Options</span>
        <button class="btn btn-sm btn-outline-primary d-sm-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse" style="border-color:#17007C;color:#17007C;">
            <i class="bi bi-funnel"></i> Filters
        </button>
    </div>

    <!-- Collapsible Body -->
    <div class="collapse d-sm-block" id="filterCollapse">
        <div class="card-body py-2">
            <form id="filterForm" method="GET" class="row g-2 align-items-center">
                <!-- Search -->
                <div class="col-12 col-sm-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search by employee name">
                </div>

                <!-- Status -->
                <div class="col-6 col-sm-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="Completed" {{ request('status')=='Completed'?'selected':'' }}>Completed</option>
                        <option value="Pending" {{ request('status')=='Pending'?'selected':'' }}>Pending</option>
                    </select>
                </div>

                <!-- Round Type -->
                <div class="col-6 col-sm-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Rounds</option>
                        <option value="regular" {{ request('type')=='regular'?'selected':'' }}>Regular (≤2)</option>
                        <option value="overtime" {{ request('type')=='overtime'?'selected':'' }}>Overtime (>2)</option>
                    </select>
                </div>

                <!-- Position -->
                <div class="col-6 col-sm-2">
                    <select name="position" class="form-select form-select-sm">
                        <option value="">All Positions</option>
                        <option value="Driver" {{ request('position')=='Driver'?'selected':'' }}>Driver</option>
                        <option value="Conductor" {{ request('position')=='Conductor'?'selected':'' }}>Conductor</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-6 col-sm-3 d-flex gap-1">
                    <button type="submit" class="btn btn-sm w-50 text-white" style="background-color:#17007C;">Apply</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary w-50" id="resetBtn">Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Round Trip Table -->
<div class="card shadow-sm">
    <div class="card-header text-white fw-bold small" style="background-color: #17007C;">
        Round Trip History
    </div>

    <div class="table-responsive overflow-auto">
        <table class="table table-bordered table-hover table-sm mb-0 text-center" style="min-width: 900px;">
            <thead class="table-light small">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                    <th>Return</th>
                    <th>Status</th>
                    <th>Round</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roundTrips as $index => $trip)
                <tr class="small">
                    <td>{{ $roundTrips->firstItem() + $index }}</td>
                    <td>{{ $trip->employee->full_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($trip->date)->format('M d, Y') }}</td>
                    <td>{{ $trip->departure ? \Carbon\Carbon::parse($trip->departure)->format('h:i A') : '-' }}</td>
                    <td>{{ $trip->arrival ? \Carbon\Carbon::parse($trip->arrival)->format('h:i A') : '-' }}</td>
                    <td>{{ $trip->return ? \Carbon\Carbon::parse($trip->return)->format('h:i A') : '-' }}</td>
                    <td>{{ ucfirst($trip->status) }}</td>
                    <td>{{ $trip->round_number }}</td>
                    <td>
                        @if($trip->round_number > 2)
                            <span class="badge bg-warning text-dark">Overtime</span>
                        @else
                            <span class="badge bg-info text-dark">Regular</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-muted small">No round trips found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center flex-wrap">
    <div class="small text-muted mb-2 mb-sm-0">
        Showing {{ $roundTrips->firstItem() }} to {{ $roundTrips->lastItem() }} of {{ $roundTrips->total() }} results
    </div>
    <nav>
        <ul class="pagination mb-0 pagination-lg gap-2 justify-content-center">
            <li class="page-item {{ $roundTrips->onFirstPage() ? 'disabled' : '' }}">
                <a class="page-link rounded-pill shadow-sm px-4" href="{{ $roundTrips->previousPageUrl() ?? '#' }}">
                    Previous
                </a>
            </li>
            <li class="page-item {{ !$roundTrips->hasMorePages() ? 'disabled' : '' }}">
                <a class="page-link rounded-pill shadow-sm px-4" href="{{ $roundTrips->nextPageUrl() ?? '#' }}">
                    Next
                </a>
            </li>
        </ul>
    </nav>
</div>

</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm-custom mx-auto">
        <div class="modal-content rounded-4 shadow-sm">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title" id="changePasswordLabel">Change Password</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('dispatcher.change.password') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small">Current Password</label>
                        <input type="password" name="current_password" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">New Password</label>
                        <input type="password" name="new_password" id="new_password" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control form-control-sm" required>
                    </div>
                    <ul class="list-unstyled small mb-0">
                        <li>• At least 8 characters</li>
                        <li>• One uppercase letter</li>
                        <li>• One lowercase letter</li>
                        <li>• One number</li>
                        <li>• One special character (@$!%*#?&)</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
    .text-custom-blue {
    color: #17007C !important;
}
/* Custom pagination style matching Deduction page */
.pagination .page-link {
    border-radius: 20px;
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    color: #17007C;
    border: 1px solid #dee2e6;
    transition: all 0.2s;
}

.pagination .page-link:hover {
    background-color: #17007C;
    color: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
}

.pagination .page-item.active .page-link {
    background-color: #17007C;
    border-color: #17007C;
    color: #fff;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
}
/* Pagination – modern pill style consistent with deduction page */
.pagination .page-link {
    border-radius: 20px;
    padding: 0.35rem 1rem;
    font-size: 0.875rem;
    color: #17007C;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 5px rgba(0,0,0,0.08);
    transition: all 0.25s ease;
}

.pagination .page-link:hover {
    background-color: #17007C;
    color: #fff;
    box-shadow: 0 3px 6px rgba(0,0,0,0.15);
}

.pagination .page-item.disabled .page-link {
    opacity: 0.6;
    pointer-events: none;
}

.pagination .page-item.active .page-link {
    background-color: #17007C;
    border-color: #17007C;
    color: #fff;
}

</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.querySelector('input[name="search"]');
    const selects = filterForm.querySelectorAll('select');
    const resetBtn = document.getElementById('resetBtn');
    let typingTimer;
    const typingDelay = 500;

    // Auto-submit on typing (after 0.5s delay)
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => filterForm.submit(), typingDelay);
        });
    }

    // Auto-submit when any dropdown changes
    selects.forEach(select => {
        select.addEventListener('change', () => filterForm.submit());
    });

    // Reset filters
    resetBtn.addEventListener('click', function (e) {
        e.preventDefault();
        filterForm.querySelectorAll('input, select').forEach(input => input.value = '');
        filterForm.submit();
    });
});
</script>


</body>
</html>
