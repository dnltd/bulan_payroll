<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Montserrat', sans-serif;
        }

        .login-page {
            background-color: #17007C;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .logo-container img {
            max-width: 180px;
            margin-bottom: 30px;
        }

        .login-card {
            position: relative;
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            color: #fff;
        }

        .login-card h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.75rem;
            line-height: 1.4;
            letter-spacing: 0.5px;
            text-align: center;
            text-transform: uppercase;
            max-width: 320px;
            margin: 0 auto;
            font-weight: 400;
        }

        /* Floating label inputs */
        .input-box {
            position: relative;
            margin-bottom: 25px;
        }

        .input-box input {
            width: 100%;
            padding: 12px 35px 12px 35px;
            background: transparent !important;
            border: none !important;
            border-bottom: 1px solid #fff !important;
            color: #fff !important;
            font-size: 1rem;
            outline: none;
            box-shadow: none !important;
        }

        .input-box input:focus {
            background: transparent !important;
            color: #fff !important;
            border-bottom: 1px solid #fff !important;
            box-shadow: none !important;
        }

        input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 1000px transparent inset !important;
            -webkit-text-fill-color: #fff !important;
            transition: background-color 9999s ease-in-out 0s;
        }

        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear,
        input[type="password"]::-webkit-credentials-auto-fill-button,
        input[type="password"]::-webkit-password-toggle-button {
            display: none;
        }

        .input-box label {
            position: absolute;
            top: 50%;
            left: 35px;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.7);
            font-size: 1rem;
            pointer-events: none;
            transition: 0.3s ease all;
        }

        .input-box input:focus ~ label,
        .input-box input:not(:placeholder-shown) ~ label {
            top: -8px;
            left: 35px;
            font-size: 0.85rem;
            color: #fff;
        }

        .input-box .icon-left {
            position: absolute;
            top: 50%;
            left: 8px;
            transform: translateY(-50%);
            color: #fff;
            font-size: 1.2rem;
            pointer-events: none;
        }

        .position-relative .toggle-password-btn {
            border: none;
            background: transparent;
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            color: #fff;
            z-index: 10;
        }

        .text-end a {
            color: #fff;
            text-decoration: none;
        }

        .text-end a:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            border-radius: 40px;
            font-weight: 500;
            background-color: #fff !important;
            color: #000 !important;
            border: none !important;
            padding: 10px 0;
        }

        .btn-login:hover,
        .btn-login:focus,
        .btn-login:active {
            background-color: #fff !important;
            color: #000 !important;
            border: none !important;
            box-shadow: none !important;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 20px;
            }
        }

        /* Responsive Swal popup */
        .swal-responsive {
            width: 90% !important;
            max-width: 400px !important;
            border-radius: 1rem !important;
            font-size: 0.95rem;
        }

        .swal2-actions {
            flex-wrap: wrap;
            gap: 0.5rem;
        }
    </style>
</head>
<body>
<div class="login-page">
    <!-- Logo -->
    <div class="logo-container text-center">
        <img src="{{ asset('images/logo.png') }}" alt="Logo">
    </div>

    <!-- Login Form -->
    <div class="login-card">
        <h2>Login</h2>

        <div class="text-center mb-4">
            <p class="login-subtitle">
                BulanTransCo Automated Payroll System
            </p>
        </div>

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf

            <!-- Email -->
            <div class="input-box">
                <i class="bi bi-envelope-fill icon-left"></i>
                <input type="email" name="email" placeholder=" " required>
                <label>Email</label>
            </div>

            <!-- Password -->
            <div class="input-box position-relative">
                <i class="bi bi-lock-fill icon-left"></i>
                <input type="password" name="password" id="password" placeholder=" " required>
                <label>Password</label>
                <button type="button" class="toggle-password-btn" onclick="togglePassword()">
                    <i class="bi bi-eye" id="toggleIcon"></i>
                </button>
            </div>

            <div class="text-end mb-3">
                <a href="{{ route('auth.verify.form') }}" class="small">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-login">Login</button>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Toggle password visibility
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

    // SweetAlert2 global function
    function showSwal(title, text = '', icon = 'info') {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonText: 'OK',
            confirmButtonColor: '#4e73df',
            timer: 5000,
            timerProgressBar: true,
            allowOutsideClick: true,
            allowEscapeKey: true,
            backdrop: true,
            customClass: { popup: 'swal-responsive' }
        });
    }

    // Laravel session alerts
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            showSwal('Success', '{{ session('success') }}', 'success');
        @elseif(session('error'))
            showSwal('Error', '{{ session('error') }}', 'error');
        @elseif($errors->any())
            showSwal('Validation Error', '{{ $errors->first() }}', 'error');
        @endif
    });
</script>
</body>
</html>
