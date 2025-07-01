<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OTP Verification | Payroll Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .logo-container {
            background-color: #17007C;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-card {
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        .card-body {
            padding: 30px;
        }
        h4 {
            text-align: center;
        }
        p {
            text-align: center;
        }
        @media (max-width: 768px) {
            .row {
                flex-direction: column-reverse;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row w-100" style="height: 100vh;">
        <!-- Logo -->
        <div class="col-md-6 logo-container">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="img-fluid" style="max-height: 250px;">
        </div>

        <!-- OTP Form -->
        <div class="col-md-6 login-container bg-white">
            <div class="card w-75 login-card">
                <div class="card-body">
                    <div class="text-start mb-4">
                        <h4>Verification Code</h4>
                        <p>Enter the OTP sent to your email.</p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('otp.verify') }}" method="POST">
                        @csrf

                        <div class="form-group mb-3">
                            <input type="text" name="otp" class="form-control" placeholder="Enter 6-digit OTP" maxlength="6" required>
                        </div>

                        <button type="submit" class="btn btn-dark w-100">Verify OTP</button>
                    </form>

                    <div class="text-center mt-3">
                        <form action="{{ route('otp.resend') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-link">Resend OTP</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
