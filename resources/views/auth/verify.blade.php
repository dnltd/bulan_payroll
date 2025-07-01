@extends('auth.layout') {{-- same layout as login.blade.php --}}

@section('content')
<div class="text-start mb-4">
    <h4>Email Verification</h4>
    <p><strong>Enter the 6-digit OTP</strong> sent to your email to continue.</p>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
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

<form action="{{ route('password.otp.check') }}" method="POST">
    @csrf

    <div class="form-group mb-3">
        <input type="text" name="otp" class="form-control" placeholder="Enter OTP Code" required>
    </div>

    <button type="submit" class="btn btn-dark w-100">Verify</button>
</form>
@endsection
