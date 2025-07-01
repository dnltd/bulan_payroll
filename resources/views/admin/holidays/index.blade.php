@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Holiday List</h4>

    <a href="{{ route('admin.holidays.create') }}" class="btn btn-primary mb-3">Add Holiday</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-3 table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($holidays as $holiday)
                <tr>
                    <td>{{ $holiday->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($holiday->date)->format('F d, Y') }}</td>
                    <td>
                        <a href="{{ route('admin.holidays.edit', $holiday->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('admin.holidays.destroy', $holiday->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Delete this holiday?')" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">No holidays found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
