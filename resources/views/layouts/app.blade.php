<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        .sidebar {
            height: 100vh;
            background-color: #17007C;
            color: white;
            padding: 20px;
            width: 250px;
            flex-shrink: 0;
        }

        .main-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100%;
        }

        .header {
            background-color: #ffffff;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .main-content {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background-color: #f8f9fa;
        }

        .sidebar .nav-link {
            color: white;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background-color: #0f0058;
            border-radius: 8px;
        }

        .sidebar .logo {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .profile-icon {
            width: 50px;
            height: 50px;
            background-color: white;
            border-radius: 50%;
            object-fit: cover;
        }

        .header h4 {
            margin: 0;
            color: #17007C;
        }

    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column">
        <div class="logo mb-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width: 80px;">
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.payroll.index') ? 'active' : '' }}" href="{{ route('admin.payroll.index') }}">
                    <i class="bi bi-cash-stack"></i> Payroll
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.employees.index') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}">
                    <i class="bi bi-people"></i> Employees
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.deductions.index') ? 'active' : '' }}" href="{{ route('admin.deductions.index') }}">
                    <i class="bi bi-percent"></i> Deductions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}" href="{{ route('admin.holidays.index') }}">
                    <i class="bi bi-calendar-event"></i> Holidays
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.attendance.index') ? 'active' : '' }}" href="{{ route('admin.attendance.index') }}">
                    <i class="bi bi-calendar-check"></i> Attendance
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.round_trip.index') ? 'active' : '' }}" href="{{ route('admin.round_trip.index') }}">
                    <i class="bi bi-truck"></i> Roundtrip
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-link nav-link text-start text-white">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Header -->
        <div class="header">
            <h4>Admin Dashboard</h4>
            <div class="d-flex align-items-center gap-3">
                @php
    $profilePic = Auth::user()->profile_picture 
        ? asset('storage/profile_pictures/' . Auth::user()->profile_picture)
        : asset('images/user.jpg');
@endphp

<img src="{{ $profilePic }}" class="profile-icon" alt="User">

            </div>
        </div>

        <!-- Page Content -->
        <div class="main-content">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')

</body>
</html>
