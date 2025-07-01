@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">Employee List</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">Add New Employee</a>

        <form action="{{ route('admin.employees.index') }}" method="GET" class="d-flex" style="max-width: 300px;">
            <input type="text" name="search" class="form-control me-2" placeholder="Search name or position..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-secondary">Search</button>
        </form>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-3 table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Salary Rate</th>
                        <th>Contact</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>{{ $employee->full_name }}</td>
                            <td>{{ $employee->position }}</td>
                            <td>₱{{ number_format($employee->salaryRate->daily_rate ?? 0, 2) }}</td>
                            <td>{{ $employee->contact_number }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-sm btn-warning me-1">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>

                                <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this employee?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No employees found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $employees->appends(['search' => request('search')])->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
