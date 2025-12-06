<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OTP Verification | Bulan Transport Cooperative System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">

    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Montserrat', sans-serif;
        }

        .login-page {
            background-color: #17007C; /* Same background */
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
            background: rgba(255, 255, 255, 0.1); /* glass effect */
            border: 2px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            color: #fff;
        }

        .login-card h2, 
        .login-card h4, 
        .login-card p {
            text-align: center;
            margin-bottom: 15px;
            color: #fff;
        }

        /* Floating label inputs */
        .input-box {
            position: relative;
            margin-bottom: 25px;
        }

        .input-box input {
            width: 100%;
            padding: 12px 10px;
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
            left: 10px;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.7);
            font-size: 1rem;
            pointer-events: none;
            transition: 0.3s ease all;
        }

        .input-box input:focus ~ label,
        .input-box input:not(:placeholder-shown) ~ label {
            top: -8px;
            left: 0;
            font-size: 0.85rem;
            color: #fff;
        }

        /* White button - no hover effect */
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

        .text-center a {
            color: #fff;
            text-decoration: none;
            font-size: 0.875rem;
            pointer-events: auto;
        }

        .text-center a.disabled {
            pointer-events: none;
            opacity: 0.5;
        }

        .text-center a:hover {
            text-decoration: underline;
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
        <h4>Verification Code</h4>
        <p>Enter the OTP code sent to your email to log in.</p>
        <p class="text-light">OTP expires in <span id="timer">05:00</span></p>

        @if (session('status'))
            <div class="alert alert-success text-dark">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger text-dark">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger text-dark">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('auth.otp.verify') }}" method="POST">
            @csrf

            <div class="input-box">
                <input type="text" name="otp" placeholder=" " maxlength="6" required>
                <label>Enter 6-digit OTP</label>
            </div>

            <button type="submit" class="btn btn-login">Verify OTP</button>
        </form>

        <div class="text-center mt-3">
            <small>Didn't receive the code?</small><br>
            <a id="resendLink" href="{{ route('auth.otp.resend') }}">Resend OTP</a>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let timerDisplay = document.getElementById("timer");
    let resendLink = document.getElementById("resendLink");
    let timeLeft = 300; // 5 minutes

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
                resendLink.classList.remove("disabled"); // enable resend link
            }
        }, 1000);
    }

    startTimer();
</script>
</body>
</html>
