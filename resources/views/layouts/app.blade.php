<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
   <style>
html, body {
    height: 100%;
    margin: 0;
}

.main-wrapper {
    display: flex;
    flex-direction: column;
    height: 100vh;
    width: 100%;
}

/* Sidebar */
.sidebar {
    height: 100vh;
    background-color: #17007C;
    color: white;
    padding: 20px;
    width: 250px;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.sidebar.collapsed {
    width: 70px;
    padding: 20px 10px;
}

.sidebar.collapsed .nav-link span { display: none; }
.sidebar.collapsed .logo-text { font-size: 0; }
.sidebar.collapsed .logo img { width: 40px !important; }

/* Logo */
.sidebar .logo {
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.sidebar .logo img { width: 50px; height: auto; }
.sidebar .logo-text {
    font-size: 0.8rem;
    font-weight: bold;
    transition: font-size 0.3s ease;
}

.sidebar .mobile-close {
    display: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: white;
}

/* Nav Links */
.sidebar .nav-link {
    color: #fff;
    margin-bottom: 3px !important;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 7px 16px !important;
    border-radius: 30px 0 0 30px;
    transition: all 0.2s ease-in-out;
    position: relative;
    z-index: 1;
    background-color: transparent;
}

.sidebar .nav-link i { font-size: 1.2rem; }

/* Expanded hover/active */
.sidebar:not(.collapsed) .nav-link:hover,
.sidebar:not(.collapsed) .nav-link.active {
    background-color: #ffffff;
    color: #17007C;
    font-weight: 600;
    width: calc(100% + 20px);
    margin-right: -20px;
    z-index: 2;
}

.sidebar:not(.collapsed) .nav-link:hover i,
.sidebar:not(.collapsed) .nav-link.active i {
    color: #17007C;
}

/* Collapsed hover/active */
.sidebar.collapsed .nav-link.active,
.sidebar.collapsed .nav-link:hover {
    background-color: #f8f9fa;
    color: #17007C;
    font-weight: 600;
    width: calc(100% + 10px);
    margin-right: -10px;
    justify-content: center;
}

.sidebar.collapsed .nav-link.active i,
.sidebar.collapsed .nav-link:hover i {
    color: #17007C;
}

/* ----------------------- */
/* LOGOUT BUTTON STYLING   */
/* ----------------------- */

/* Default */
.logout-link {
    color: #fff;
    background-color: transparent;
    padding: 7px 16px;
    border-radius: 30px 0 0 30px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.95rem;
    transition: all 0.2s ease-in-out;
}

.logout-link i {
    font-size: 1.2rem;
    color: #fff;
}

/* Expanded hover/active */
.sidebar:not(.collapsed) .logout-link:hover,
.sidebar:not(.collapsed) .logout-link.active {
    background-color: #ffffff;
    color: #17007C;
    font-weight: 600;
    width: calc(100% + 20px);
    margin-right: -20px;
    z-index: 2;
}


.sidebar:not(.collapsed) .logout-link:hover i,
.sidebar:not(.collapsed) .logout-link.active i {
    color: #17007C;
}


/* Collapsed hover */
.sidebar.collapsed .logout-link:hover {
    background-color: #f8f9fa;
    color: #17007C;
    font-weight: 600;
    width: calc(100% + 10px);
    margin-right: -10px;
    justify-content: center;
}
.sidebar.collapsed .logout-link:hover i {
    color: #17007C;
}

/* Hide logout text on collapse */
.sidebar.collapsed .logout-link span {
    display: none;
}

/* Header */
.header {
    background-color: #fff;
    padding: 12px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    position: sticky;
    top: 0;
    z-index: 1030;
}

/* Main Content */
.main-content {
    flex-grow: 1;
    overflow-y: auto;
    padding: 1.5rem;
    background-color: #f8f9fa;
}

/* Profile */
.profile-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    cursor: pointer;
    transition: 0.2s;
}
.profile-icon:hover { transform: scale(1.05); }

.dropdown-menu {
    border-radius: 12px;
    border: none;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    padding: 8px 0;
}

/* Mobile Sidebar */
@media (max-width: 991px) {
    .sidebar {
        position: fixed;
        top: 0;
        left: -250px;
        width: 250px;
        height: 100%;
        z-index: 1050;
        padding: 15px;
        overflow: hidden;
    }

    .sidebar.active { left: 0; }
    body.sidebar-open { overflow: hidden; }

    .sidebar .mobile-close { display: block; }
    .sidebar .logo-text { font-size: 0.6rem; }

    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.4);
        display: none;
        z-index: 1040;
    }
    .sidebar-overlay.active { display: block; }

    .sidebar .nav-link {
        width: 100%;
        padding: 12px 16px !important;
        margin: 0;
        border-radius: 0;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        background-color: #ffffff;
        color: #17007C;
        font-weight: 600;
        border-radius: 30px 0 0 30px;
    }
}
</style>


</head>
<body>
<div class="d-flex">

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column" id="sidebar">
        <div class="logo mb-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
            <span class="logo-text">Bulan Transport Cooperative Payroll</span>
            <i class="bi bi-x mobile-close" id="sidebarClose"></i>
        </div>

        <ul class="nav flex-column">
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-house"></i> <span>Dashboard</span></a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.payroll.index') ? 'active' : '' }}" href="{{ route('admin.payroll.index') }}"><i class="bi bi-cash-stack"></i> <span>Payroll</span></a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.employees.index') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}"><i class="bi bi-people"></i> <span>Employees</span></a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.deductions.index') ? 'active' : '' }}" href="{{ route('admin.deductions.index') }}"><i class="bi bi-percent"></i> <span>Deductions</span></a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.holidays.index') ? 'active' : '' }}" href="{{ route('admin.holidays.index') }}"><i class="bi bi-calendar-event"></i> <span>Holidays</span></a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.attendance.index') ? 'active' : '' }}" href="{{ route('admin.attendance.index') }}"><i class="bi bi-calendar-check"></i> <span>Attendance</span></a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.round_trip.index') ? 'active' : '' }}" href="{{ route('admin.round_trip.index') }}"><i class="bi bi-truck"></i> <span>Roundtrip</span></a></li>
    <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}"><i class="bi bi-gear"></i> <span>Settings</span></a></li>

    <!-- 🔽 LOGOUT BUTTON AT BOTTOM -->
    <li class="nav-item logout-item mt-auto">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="nav-link logout-link  text-start">
            <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
        </button>
    </form>
</li>


</ul>

    </div>

    <!-- Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Header -->
        <div class="header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                
            </div>

            <div class="d-flex align-items-center gap-3">
                @include('partials.searchbar')

                @php
                $profilePicture = Auth::user()->profile_picture;
                $defaultPath = asset('images/default.jpg');
                $storedPath = public_path('storage/profile_pictures/' . $profilePicture);

                $profilePath = ($profilePicture && file_exists($storedPath))
                ? asset('storage/profile_pictures/' . $profilePicture)
                : $defaultPath;
                @endphp

                <div class="dropdown">
                    <a href="#" id="profileDropdown" data-bs-toggle="dropdown">
                        <img src="{{ $profilePath }}" class="profile-icon" alt="User">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');

    sidebarToggle.addEventListener('click', () => {
        if(window.innerWidth >= 992){
            sidebar.classList.toggle('collapsed');
        } else {
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.classList.add('sidebar-open');
        }
    });
    sidebarClose.addEventListener('click', () => closeSidebar());
    overlay.addEventListener('click', () => closeSidebar());

    function closeSidebar(){
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
    }
</script>
@stack('scripts')
</body>
</html>
