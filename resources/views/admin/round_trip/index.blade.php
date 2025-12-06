@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Round Trip Monitoring</h2>
            <p class="text-muted small mb-0">Track driver and conductor trips with departure, arrival, and return details</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.round_trip.index') }}" class="card shadow-sm border-0 mb-4">
        <div class="card-body row g-3 align-items-end">

            {{-- Position Filter --}}
            <div class="col-md-4">
                <label for="position" class="form-label fw-semibold small">Position</label>
                <select name="position" id="position" class="form-select form-select-sm shadow-sm border-0">
                    <option value="">All Positions</option>
                    <option value="Driver" {{ request('position') == 'Driver' ? 'selected' : '' }}>Driver</option>
                    <option value="Conductor" {{ request('position') == 'Conductor' ? 'selected' : '' }}>Conductor</option>
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
                <a href="{{ route('admin.round_trip.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm px-3">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>

        </div>
    </form>

    {{-- Round Trip Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-3 table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white deduction-table mb-0">
                <thead style="background-color:#17007C; color:white;">
                    <tr>
                        <th>No.</th>
                        <th>Employee</th>
                        <th>Role</th>
                        <th>Date</th>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th>Return</th>
                        <th>Round #</th>
                        <th>Holiday</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
@forelse($roundTrips as $index => $trip)
    <tr>
        {{-- ✅ Correct pagination numbering --}}
        <td>{{ $roundTrips->firstItem() + $index }}</td>
        <td>{{ $trip->employee->full_name ?? 'N/A' }}</td>
        <td>{{ $trip->employee->position ?? 'N/A' }}</td>
        
        {{-- Date --}}
        <td>{{ \Carbon\Carbon::parse($trip->date)->format('M d, Y') }}</td>
        
        {{-- Time Columns --}}
        <td>{{ $trip->departure ? \Carbon\Carbon::parse($trip->departure)->format('h:i A') : '-' }}</td>
        <td>{{ $trip->arrival ? \Carbon\Carbon::parse($trip->arrival)->format('h:i A') : '-' }}</td>
        <td>{{ $trip->return ? \Carbon\Carbon::parse($trip->return)->format('h:i A') : '-' }}</td>
        
        <td>{{ $trip->round_number }}</td>
        <td>{{ $trip->holiday->name ?? 'None' }}</td>
        <td>
            <span class="badge bg-{{ $trip->status === 'Completed' ? 'success' : 'warning' }}">
                {{ $trip->status }}
            </span>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center text-muted">No round trips found.</td>
    </tr>
@endforelse
</tbody>


            </table>
        </div>

        {{-- Pagination --}}
        @if ($roundTrips instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="d-flex justify-content-between align-items-center p-3 flex-wrap">
                <div class="small text-muted mb-2">
                    Showing {{ $roundTrips->firstItem() }} to {{ $roundTrips->lastItem() }} of {{ $roundTrips->total() }} results
                </div>
                <nav>
                    <ul class="pagination mb-0 pagination-lg gap-2">
                        <li class="page-item {{ $roundTrips->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link rounded-pill shadow-sm px-4" 
                               href="{{ $roundTrips->previousPageUrl() ?? '#' }}">
                                Previous
                            </a>
                        </li>
                        <li class="page-item {{ !$roundTrips->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link rounded-pill shadow-sm px-4" 
                               href="{{ $roundTrips->nextPageUrl() ?? '#' }}">
                                Next
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
    </div>
</div>

{{-- Styles --}}
<style>
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
@endsection
