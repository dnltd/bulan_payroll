<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>OTP Verification</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background: white; border-radius: 8px; overflow: hidden;">
        <div style="background-color: #17007C; color: white; text-align: center; padding: 20px;">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" style="max-height: 80px; margin-bottom: 10px;">
            <h2 style="margin: 0;">Bulan Transport Cooperative</h2>
            <p style="margin: 0;">Secure Login Verification</p>
        </div>
        <div style="padding: 30px;">
            <h3 style="color: #17007C;">Hello!</h3>
            <p style="font-size: 16px; color: #333;">Your One-Time Password (OTP) is:</p>
            <p style="font-size: 30px; font-weight: bold; color: #17007C; text-align: center; margin: 20px 0;">{{ $otp }}</p>
            <p style="font-size: 14px; color: #555;">This code is valid for {{ $ttl }} minutes. Please do not share it with anyone.</p>
            <p style="font-size: 13px; color: #aaa; text-align: center; margin-top: 40px;">&copy; {{ date('Y') }} Bulan Transport Cooperative. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
