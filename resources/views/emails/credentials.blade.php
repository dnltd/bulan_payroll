<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Account Credentials</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
        <!-- Header -->
        <div style="background-color: #17007C; color: #fff; padding: 20px; text-align: center;">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" style="max-height: 80px; margin-bottom: 10px;">
            <h2 style="margin: 0;">Bulan Transport Cooperative</h2>
            <p style="margin: 0; font-size: 14px;">Account Credentials</p>
        </div>

        <!-- Body -->
        <div style="padding: 30px; background-color: #ffffff;">
            <h3 style="color: #17007C;">Welcome to Bulan Payroll System!</h3>
            <p style="font-size: 16px; color: #333;">Your <strong>{{ ucfirst($role) }}</strong> account has been created with the following credentials:</p>

            <p style="font-size: 15px; color: #333;"><strong>Email:</strong> {{ $email }}</p>
            <p style="font-size: 15px; color: #333;"><strong>Password:</strong> {{ $password }}</p>
            <p style="font-size: 15px; color: #333;"><strong>Role:</strong> {{ $role }}</p>

            <p style="font-size: 14px; color: #555; margin-top: 20px;">
                Please log in and change your password immediately for security.
            </p>

            <p style="font-size: 13px; color: #aaa; text-align: center; margin-top: 40px;">
                &copy; {{ date('Y') }} Bulan Transport Cooperative. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
