<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auth | Payroll Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .logo-container {
            background-color: #17007C;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .auth-card {
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
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
        <!-- Logo on the left -->
        <div class="col-md-6 logo-container">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="img-fluid" style="max-height: 250px;">
        </div>

        <!-- Auth Form on the right -->
        <div class="col-md-6 form-container bg-white">
            <div class="card w-75 auth-card">
                <div class="card-body">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
