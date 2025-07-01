@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Edit Holiday</h4>

    <form action="{{ route('admin.holidays.update', $holiday->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ $holiday->name }}" required>
        </div>

        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="{{ $holiday->date }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('admin.holidays.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
