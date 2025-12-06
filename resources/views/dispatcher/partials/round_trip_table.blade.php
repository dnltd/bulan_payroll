<table class="table table-bordered table-hover table-sm mb-0">
    <thead class="table-light small text-center">
        <tr>
            <th>#</th>
            <th>Employee Name</th>
            <th>Date</th>
            <th>Departure</th>
            <th>Arrival</th>
            <th>Return</th>
            <th>Status</th>
            <th>Round</th>
            <th>Type</th>
        </tr>
    </thead>
    <tbody class="small text-center">
        @forelse ($roundTrips as $index => $trip)
            <tr>
                <td>{{ ($roundTrips->currentPage() - 1) * $roundTrips->perPage() + $index + 1 }}</td>
                <td>{{ $trip->employee->full_name }}</td>
                <td>{{ $trip->date }}</td>
                <td>{{ $trip->departure ?? '-' }}</td>
                <td>{{ $trip->arrival ?? '-' }}</td>
                <td>{{ $trip->return ?? '-' }}</td>
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
                <td colspan="9" class="text-muted">No round trips found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if($roundTrips->hasPages())
    <div class="mt-2 px-2">
        {{ $roundTrips->links('pagination::bootstrap-5') }}
    </div>
@endif
