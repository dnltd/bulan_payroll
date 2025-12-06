<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Password | Bulan Transport Cooperative System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">

    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            color: #fff;
        }

        .reset-page {
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

        .reset-card {
            position: relative;
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        /* Text inside card */
        .reset-card h4,
        .reset-card p,
        .reset-card ul,
        .reset-card li,
        .reset-card .alert,
        .input-box input,
        .input-box label {
            color: #fff !important;
            text-shadow: none !important; /* Remove any text shadow */
        }

        /* Floating label inputs with lock icon */
        .input-box {
            position: relative;
            margin-bottom: 25px;
        }

        .input-box input {
            width: 100%;
            padding: 12px 35px 12px 35px; /* space for icons */
            background: transparent !important;
            border: none;
            border-bottom: 1px solid #fff;
            color: #fff;
            font-size: 1rem;
            outline: none;
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
            text-shadow: none !important;
        }

        .input-box input:focus ~ label,
        .input-box input:not(:placeholder-shown) ~ label {
            top: -8px;
            left: 35px;
            font-size: 0.85rem;
            color: #fff;
        }

        /* Lock icon */
        .input-box .icon-left {
            position: absolute;
            top: 50%;
            left: 8px;
            transform: translateY(-50%);
            color: #fff;
            font-size: 1.2rem;
            pointer-events: none;
        }

        /* Eye toggle button */
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

        /* White button */
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

        .policy-list {
            text-align: left;
            font-size: 0.85rem;
            color: rgba(255,255,255,0.9);
        }

        /* Remove default browser password reveal button */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
        input[type="password"]::-webkit-credentials-auto-fill-button,
        input[type="password"]::-webkit-password-toggle-button {
            display: none;
        }

        @media (max-width: 576px) {
            .reset-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div class="reset-page text-center">
    <!-- Logo -->
    <div class="logo-container text-center">
        <img src="{{ asset('images/logo.png') }}" alt="Logo">
    </div>

    <!-- Reset Password Card -->
    <div class="reset-card">
        <h4>Create New Password</h4>
        <p>Enter your new password.</p>

        {{-- Flash messages --}}
        @if(session('error'))
            <div class="alert alert-danger text-dark">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success text-dark" id="successMessage">{{ session('success') }}</div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="alert alert-danger text-dark">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('auth.password.reset.submit') }}">
            @csrf
            <input type="hidden" name="email" value="{{ old('email', session('reset_email')) }}">

            <!-- New Password -->
            <div class="input-box position-relative">
                <i class="bi bi-lock-fill icon-left"></i>
                <input type="password" name="password" id="password" placeholder=" " required minlength="8" maxlength="20" onkeyup="checkStrength(this.value)">
                <label for="password">New Password</label>
                <button type="button" class="toggle-password-btn" onclick="togglePassword('password','toggleIcon')">
                    <i class="bi bi-eye" id="toggleIcon"></i>
                </button>
                <small id="strengthMessage" class="text-white small mt-1 d-block"></small>
            </div>

            <!-- Confirm Password -->
            <div class="input-box position-relative">
                <i class="bi bi-lock-fill icon-left"></i>
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder=" " required minlength="8" maxlength="20">
                <label for="password_confirmation">Confirm Password</label>
                <button type="button" class="toggle-password-btn" onclick="togglePassword('password_confirmation','toggleIconConfirm')">
                    <i class="bi bi-eye" id="toggleIconConfirm"></i>
                </button>
            </div>

            <!-- Password Policy -->
            <div class="mt-3 policy-list">
                <p class="fw-bold mb-1">Password Policy:</p>
                <ul class="list-unstyled mb-0">
                    <li>8 to 20 characters long</li>
                    <li>At least one uppercase and one lowercase letter</li>
                    <li>Include numbers</li>
                    <li>At least one special character (@, #, !, * or $)</li>
                </ul>
            </div>

            <button type="submit" class="btn btn-login mt-4">Reset Password</button>
        </form>
    </div>
</div>

<!-- JS Scripts -->
<script>
    function checkStrength(password) {
        const message = document.getElementById('strengthMessage');
        const strongRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$!*])[A-Za-z\d@#$!*]{8,20}$/;

        if (strongRegex.test(password)) {
            message.textContent = "Strong password ✅";
            message.classList.remove('text-danger');
            message.classList.add('text-success');
        } else {
            message.textContent = "Weak password ❌";
            message.classList.remove('text-success');
            message.classList.add('text-danger');
        }
    }

    function togglePassword(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const toggleIcon = document.getElementById(iconId);

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

    // Redirect after success
    @if(session('success'))
        setTimeout(function() {
            window.location.href = "{{ route('login') }}";
        }, 3000);
    @endif
</script>
</body>
</html>
