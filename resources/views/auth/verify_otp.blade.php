<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP | Bulan Transport Cooperative System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">

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

        .login-card h4,
        .login-card p {
            color: #fff;
            text-align: center;
        }

        /* Floating label input */
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

        /* Button */
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

        .alert {
            color: #000 !important;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div class="login-page">
    <!-- Logo -->
    <div class="logo-container text-center">
        <img src="{{ asset('images/logo.png') }}" alt="Logo">
    </div>

    <!-- OTP Card -->
    <div class="login-card">
        <div class="text-start mb-4">
            <h4>OTP Verification</h4>
            <p>An OTP has been sent to your email to reset your password.</p>
            <p class="text-light">OTP expires in <span id="timer">05:00</span></p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('auth.verify.otp.submit') }}">
            @csrf
            <div class="input-box">
                <i class="bi bi-shield-lock-fill icon-left"></i>
                <input type="text" name="otp" placeholder=" " required autofocus>
                <label>Enter OTP</label>
            </div>
            <button type="submit" class="btn btn-login">Verify OTP</button>
        </form>

        <div class="text-center mt-4">
            Didn't receive the code?<br>
            <form method="POST" action="{{ route('auth.resend.otp') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-link p-0 m-0 align-baseline text-white">Resend OTP</button>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Timer Script -->
<script>
    let timerDisplay = document.getElementById("timer");
    let timeLeft = 300; // 5 minutes in seconds

    function startTimer() {
        let timer = setInterval(() => {
            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            seconds = seconds < 10 ? "0" + seconds : seconds;
            timerDisplay.textContent = `${minutes}:${seconds}`;
            timeLeft--;

            if (timeLeft < 0) {
                clearInterval(timer);
                timerDisplay.textContent = "Expired";
            }
        }, 1000);
    }

    startTimer();
</script>
</body>
</html>
