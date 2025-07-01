@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Add Holiday</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.holidays.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="{{ old('date') }}" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('admin.holidays.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection
