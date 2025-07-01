@extends('layouts.app')

@section('content')
<div class="container py-4">

    <!-- Summary Cards -->
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 text-center">
                <div class="card-body">
                    <i class="bi bi-people-fill fs-2 text-primary"></i>
                    <h6 class="mt-2 text-muted">Total Employees</h6>
                    <h3 class="fw-bold">{{ $totalEmployees }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 text-center">
                <div class="card-body">
                    <i class="bi bi-wallet2 fs-2 text-success"></i>
                    <h6 class="mt-2 text-muted">Total Payroll</h6>
                    <h3 class="fw-bold">₱{{ number_format($totalPayroll, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 text-center">
                <div class="card-body">
                    <i class="bi bi-dash-circle fs-2 text-danger"></i>
                    <h6 class="mt-2 text-muted">Total Deductions</h6>
                    <h3 class="fw-bold">₱{{ number_format($totalDeductions, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4 text-center">
                <div class="card-body">
                    <i class="bi bi-person-check fs-2 text-info"></i>
                    <h6 class="mt-2 text-muted">Attendance Today</h6>
                    <h3 class="fw-bold">{{ $totalAttendance }}</h3>
                </div>
            </div>
        </div>
    </div>


    <!-- Upcoming Holidays -->
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h5 class="card-title">Upcoming Holidays</h5>
                    @if(count($recentHolidays) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($recentHolidays as $holiday)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-calendar-event text-danger me-2"></i>{{ $holiday->name }}
                                    </div>
                                    <span class="badge bg-danger rounded-pill">
                                        {{ \Carbon\Carbon::parse($holiday->date)->format('M d, Y') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No upcoming holidays</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
