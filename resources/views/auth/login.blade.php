<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Payroll Management System</title>
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
        @media (max-width: 768px) {
            .row {
                flex-direction: column-reverse;
            }
        }
        .toggle-password-btn {
            border: none;
            background: transparent;
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            z-index: 10;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row w-100" style="height: 100vh;">
        <!-- Logo on the left -->
        <div class="col-md-6 logo-container">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="img-fluid" style="max-height: 250px;">
        </div>

        <!-- Login Form on the right -->
        <div class="col-md-6 login-container bg-white">
            <div class="card w-75 login-card">
                <div class="card-body">
                    <div class="text-start mb-4">
                        <h4>Welcome!</h4>
                        <p><strong>Log in to</strong><br>Payroll Management System of Bulan Transport Cooperative</p>
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

                    <form action="{{ route('login.submit') }}" method="POST">
                        @csrf

                        <div class="form-group mb-3">
                            <input type="email" name="email" class="form-control" placeholder="Enter your Email" required>
                        </div>

                        <div class="form-group mb-3 position-relative">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter your Password" required>
                            <button type="button" class="toggle-password-btn" onclick="togglePassword()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>

                        <div class="text-end mb-3">
    <a href="{{ route('password.request') }}" class="text-decoration-none">Forgot Password?</a>
</div>


                        <button type="submit" class="btn btn-dark w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Password toggle script -->
<script>
    function togglePassword() {
        const passwordInput = document.getElementById("password");
        const toggleIcon = document.getElementById("toggleIcon");

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = "password";
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        }
    }
</script>
</body>
</html>
