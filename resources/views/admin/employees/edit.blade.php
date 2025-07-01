@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">Edit Employee</h4>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required value="{{ old('full_name', $employee->full_name) }}">
                </div>

                <div class="mb-3">
                    <label for="position" class="form-label">Position</label>
                    <select name="position" class="form-select" required>
                        @foreach(['General Manager', 'Secretary', 'Treasurer', 'Inspector', 'Dispatcher', 'Driver', 'Conductor'] as $position)
                            <option value="{{ $position }}" {{ $employee->position == $position ? 'selected' : '' }}>
                                {{ $position }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea name="address" class="form-control" required>{{ old('address', $employee->address) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="contact_number" class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" required value="{{ old('contact_number', $employee->contact_number) }}">
                </div>

                <div class="mb-3">
                    <label for="salary_rates_id" class="form-label">Salary Rate</label>
                    <select name="salary_rates_id" class="form-select" required>
                        @foreach($salaryRates as $rate)
                            <option value="{{ $rate->id }}" {{ $employee->salary_rates_id == $rate->id ? 'selected' : '' }}>
                                {{ $rate->position }} - ₱{{ number_format($rate->daily_rate, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Update Employee
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
