<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Successful</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Login Successful',
            text: 'Welcome , {{ session('name') }}!',
            showConfirmButton: false,
            timer: 2000
        }).then(() => {
            @if (session('role') === 'admin')
                window.location.href = "{{ route('admin.dashboard') }}";
            @elseif (session('role') === 'dispatcher')
                window.location.href = "{{ route('dispatcher.dashboard') }}";
            @else
                window.location.href = "/login";
            @endif
        });
    </script>
</body>
</html>
