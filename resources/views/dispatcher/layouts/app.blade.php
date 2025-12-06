<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dispatcher') | Payroll System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f4f4;
        }
        .wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: start;
            align-items: center;
            padding-top: 40px;
        }
        .logo {
            max-height: 150px;
            margin-bottom: 20px;
        }
        .card-box {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .toggle-password-btn {
            position: absolute;
            top: 50%;
            right: 10px;
            border: none;
            background: transparent;
            transform: translateY(-50%);
            z-index: 10;
        }
    </style>

    @yield('styles')
</head>
<body>

<div class="container wrapper">
    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">

    @yield('content')
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@yield('scripts')
</body>
</html>
 