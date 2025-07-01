@extends('auth.layout')

@section('content')
<div class="text-start mb-4">
    <h4 class="fw-bold">Create New Password</h4>
    <p class="text-muted">Set your new password so you can Log In and access the system.</p>
</div>

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <div class="form-group mb-3">
        <input type="password" name="password" class="form-control" placeholder="New Password" required>
    </div>

    <div class="form-group mb-4">
        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" required>
    </div>

    <button type="submit" class="btn btn-dark w-100">Enter Password</button>
</form>

<div class="mt-4">
    <p class="fw-bold mb-1">Password Policy:</p>
    <ul class="text-muted mb-0">
        <li>Length must be between 8 to 20 characters</li>
        <li>A combination of upper and lower case letters</li>
        <li>Contain letters and numbers</li>
        <li>At least one special character such as @, #, !, * or $</li>
    </ul>
</div>
@endsection
