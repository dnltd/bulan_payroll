@extends('auth.layout')

@section('content')
<div class="text-start mb-4">
    <h4>Forgot Password?</h4>
    <p><strong>Enter your registered email</strong> and we'll send you an OTP to reset your password.</p>
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

<form method="POST" action="{{ route('password.send') }}">
    @csrf

    <div class="form-group mb-3">
        <input type="email" name="email" class="form-control" placeholder="Enter your Email" required>
    </div>

    <button type="submit" class="btn btn-dark w-100">Enter</button>
</form>
@endsection
