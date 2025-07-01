@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Round Trip Monitoring</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.round_trip.index') }}" class="row mb-4 g-2">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by employee name..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="{{ request('date', \Carbon\Carbon::now()->toDateString()) }}">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">
                <i class="bi bi-search"></i> Filter
            </button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('admin.round_trip.index') }}" class="btn btn-secondary w-100">
                <i class="bi bi-x-circle"></i> Reset
            </a>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-3 table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Role</th>
                    <th>Date</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                    <th>Return</th>
                    <th>Round #</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roundTrips as $index => $trip)
                    <tr>
                        <td>{{ $index + $roundTrips->firstItem() }}</td>
                        <td>{{ $trip->employee->full_name }}</td>
                        <td>{{ $trip->employee->position }}</td>
                        <td>{{ \Carbon\Carbon::parse($trip->date)->format('M d, Y') }}</td>
                        <td>{{ $trip->departure ?? '-' }}</td>
                        <td>{{ $trip->arrival ?? '-' }}</td>
                        <td>{{ $trip->return ?? '-' }}</td>
                        <td>{{ $trip->round_number }}</td>
                        <td>
                            <span class="badge bg-{{ $trip->status === 'Completed' ? 'success' : 'warning' }}">
                                {{ $trip->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No round trips found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $roundTrips->links() }}
    </div>
</div>
@endsection
