@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">Attendance Records</h4>

    <a href="{{ route('admin.attendance.scan') }}" class="btn btn-primary mb-3">
        <i class="bi bi-camera"></i> Scan Face for Attendance
    </a>

    <div class="card shadow-sm">
        <div class="card-body p-3 table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Holiday</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $a)
                        @php
                            $isToday = \Carbon\Carbon::parse($a->date)->isToday();
                            $isHoliday = \App\Models\Holiday::whereDate('date', $a->date)->exists();
                            $holidayName = \App\Models\Holiday::whereDate('date', $a->date)->value('name');
                        @endphp
                        <tr class="{{ $isToday ? 'table-success' : '' }}">
                            <td>{{ $a->employee->full_name ?? 'Unknown' }}</td>
                            <td>{{ \Carbon\Carbon::parse($a->date)->format('M d, Y') }}</td>
                            <td>{{ $a->time_in ? \Carbon\Carbon::parse($a->time_in)->format('h:i A') : '-' }}</td>
                            <td>{{ $a->time_out ? \Carbon\Carbon::parse($a->time_out)->format('h:i A') : '-' }}</td>
                            <td>
                                @if ($isHoliday)
                                    <span class="badge bg-danger">{{ $holidayName }}</span>
                                @else
                                    <span class="badge bg-secondary">Regular</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No attendance records.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
