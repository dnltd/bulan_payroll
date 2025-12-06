@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Page Title --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Admin Settings</h4>
        <p class="text-muted small mb-0">Manage your profile, accounts, salary rates, and password settings</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger shadow-sm">{{ session('error') }}</div>
    @endif

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4 shadow-sm" id="settingsTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#profile">Profile Management</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#accounts">Account Management</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#salary">Salary Rate Management</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#password">Change Password</a></li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content bg-white p-4 rounded shadow-sm">

        {{-- PROFILE MANAGEMENT --}}
<div class="tab-pane fade show active" id="profile">
    <div class="mb-3">
        <h5 class="fw-bold">Profile Management</h5>
    <p class="text-muted small mb-0">Update your personal information and profile picture below. All changes are saved immediately.</p>
</div>

<div class="card shadow-sm rounded p-3">
    <form method="POST" action="{{ route('admin.settings.profile') }}" enctype="multipart/form-data">
        @csrf
        {{-- Name Fields --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">First Name</label>
                <input type="text" name="first_name" value="{{ Auth::user()->first_name }}" class="form-control shadow-sm">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Middle Name</label>
                <input type="text" name="middle_name" value="{{ Auth::user()->middle_name }}" class="form-control shadow-sm">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Last Name</label>
                <input type="text" name="last_name" value="{{ Auth::user()->last_name }}" class="form-control shadow-sm">
            </div>
        </div>

        {{-- Contact Info --}}
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" value="{{ Auth::user()->email }}" class="form-control shadow-sm">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Contact Number</label>
                <input type="number" name="contact_number" value="{{ Auth::user()->employee->contact_number ?? '' }}" class="form-control shadow-sm" placeholder="Enter digits only">
                <small class="text-muted">Format: 09123456789</small>
            </div>
        </div>

        {{-- Profile Picture --}}
        <div class="row g-3 mb-3 align-items-center">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Upload Profile Picture</label>
                <input type="file" name="profile_picture" class="form-control shadow-sm" onchange="previewProfile(this)">
                <small class="text-muted">Max size 2MB. JPG, PNG allowed.</small>
            </div>
            <div class="col-md-4 text-center">
                <label class="form-label fw-semibold">Preview</label><br>
                <img id="profilePreview" 
                     src="{{ Auth::user()->profile_picture ? asset('storage/profile_pictures/' . Auth::user()->profile_picture) : 'https://via.placeholder.com/120' }}" 
                     alt="Preview" 
                     class="border rounded shadow-sm" 
                     style="max-height: 120px; max-width: 120px;">
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="row">
            <div class="col-md-4">
                <button type="submit" class="btn btn-theme shadow-sm w-100">Update Profile</button>
            </div>
        </div>
    </form>
</div>

{{-- Script for Preview --}}
<script>
function previewProfile(input) {
    const reader = new FileReader();
    reader.onload = e => document.getElementById('profilePreview').src = e.target.result;
    reader.readAsDataURL(input.files[0]);
}
</script>
        </div>

        {{-- ACCOUNT MANAGEMENT --}}
        <div class="tab-pane fade" id="accounts">
            @include('admin.settings.accounts')
        </div>

        {{-- SALARY MANAGEMENT --}}
        <div class="tab-pane fade" id="salary">
            @include('admin.settings.salary')
        </div>

        {{-- CHANGE PASSWORD --}}
<div class="tab-pane fade" id="password">
    {{-- Title and Subtitle --}}
    <div class="mb-3">
        <h5 class="fw-bold">Change Your Password</h5>
        <p class="text-muted small mb-0">Ensure your new password meets all requirements and confirm it below.</p>
    </div>
    <form method="POST" action="{{ route('admin.settings.changePassword') }}" id="changePasswordForm">
        @csrf
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Current Password</label>
                <input type="password" name="current_password" class="form-control shadow-sm" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control shadow-sm" required>
                <small id="passwordHelp" class="form-text text-muted"></small>
                <div class="progress mt-1" style="height: 6px;">
                    <div id="passwordStrength" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                </div>

                {{-- Requirements --}}
                <ul class="mt-2 small mb-0" id="passwordRequirements">
                    <li id="req-length" class="text-danger">At least 8 characters</li>
                    <li id="req-upper" class="text-danger">At least one uppercase letter (A-Z)</li>
                    <li id="req-lower" class="text-danger">At least one lowercase letter (a-z)</li>
                    <li id="req-number" class="text-danger">At least one number (0-9)</li>
                    <li id="req-special" class="text-danger">At least one special character (@$!%*#?&)</li>
                    <li id="req-match" class="text-danger">Passwords must match</li>
                </ul>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Confirm New Password</label>
                <input type="password" name="new_password_confirmation" id="confirm_password" class="form-control shadow-sm" required>
            </div>
        </div>
        <button type="submit" id="changePasswordBtn" class="btn btn-theme shadow-sm" disabled>Change Password</button>
    </form>
</div>

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Password Strength & Validation --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
    const passwordInput = document.getElementById("new_password");
    const confirmInput = document.getElementById("confirm_password");
    const strengthBar = document.getElementById("passwordStrength");
    const passwordHelp = document.getElementById("passwordHelp");
    const changeBtn = document.getElementById("changePasswordBtn");

    const reqLength = document.getElementById("req-length");
    const reqUpper = document.getElementById("req-upper");
    const reqLower = document.getElementById("req-lower");
    const reqNumber = document.getElementById("req-number");
    const reqSpecial = document.getElementById("req-special");
    const reqMatch = document.getElementById("req-match");

    function validatePassword() {
        const val = passwordInput.value;
        const confirmVal = confirmInput.value;
        let strength = 0;

        if (val.length >= 8) { strength++; reqLength.classList.replace("text-danger", "text-success"); } else { reqLength.classList.replace("text-success", "text-danger"); }
        if (/[A-Z]/.test(val)) { strength++; reqUpper.classList.replace("text-danger", "text-success"); } else { reqUpper.classList.replace("text-success", "text-danger"); }
        if (/[a-z]/.test(val)) { strength++; reqLower.classList.replace("text-danger", "text-success"); } else { reqLower.classList.replace("text-success", "text-danger"); }
        if (/[0-9]/.test(val)) { strength++; reqNumber.classList.replace("text-danger", "text-success"); } else { reqNumber.classList.replace("text-success", "text-danger"); }
        if (/[@$!%*#?&]/.test(val)) { strength++; reqSpecial.classList.replace("text-danger", "text-success"); } else { reqSpecial.classList.replace("text-success", "text-danger"); }

        // Confirm password check
        if (val && confirmVal && val === confirmVal) {
            reqMatch.classList.replace("text-danger", "text-success");
        } else {
            reqMatch.classList.replace("text-success", "text-danger");
        }

        let percentage = (strength / 5) * 100;
        let color = "", text = "";
        switch (strength) {
            case 0: case 1: text="Very Weak"; color="bg-danger"; break;
            case 2: text="Weak"; color="bg-warning"; break;
            case 3: text="Moderate"; color="bg-info"; break;
            case 4: text="Strong"; color="bg-primary"; break;
            case 5: text="Very Strong"; color="bg-success"; break;
        }
        strengthBar.style.width = percentage + "%";
        strengthBar.className = "progress-bar " + color;
        passwordHelp.textContent = "Strength: " + text;

        // Enable button only if password is very strong and matches confirmation
        changeBtn.disabled = !(strength === 5 && val === confirmVal);
    }

    passwordInput.addEventListener("input", validatePassword);
    confirmInput.addEventListener("input", validatePassword);

    // SweetAlert Notifications
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            confirmButtonColor: '#17007C',
            timer: 2500,
            timerProgressBar: true
        });
    @elseif(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
            confirmButtonColor: '#17007C',
            timer: 2500,
            timerProgressBar: true
        });
    @endif
});
</script>



<style>
/* Button Theme */
.btn-theme {
    background-color: #17007C; /* Always blue */
    color: #fff; /* Always white text */
    border: none;
    border-radius: 0.5rem;
    padding: 0.55rem 1.25rem;
    font-weight: 600;
    transition: all 0.2s ease-in-out;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* subtle shadow even when not hovered */
}

.btn-theme:hover {
    background-color: #17007C; /* Keep the same blue */
    color: #fff; 
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.2); /* stronger shadow on hover */
}


/* Active Tab */
.nav-tabs .nav-link.active {
    background-color: #17007C !important;
    color: #fff !important;
    font-weight: 600;
    border-color: #17007C !important;
}

/* Progress bar */
.progress {
    border-radius: 0.25rem;
    overflow: hidden;
}
.progress-bar {
    height: 6px;
}

/* Password Requirement List */
#passwordRequirements li.text-success::before {
    content: "✔ ";
    color: green;
}
#passwordRequirements li.text-danger::before {
    content: "✖ ";
    color: red;
}

/* Tab Content Cards */
.tab-content .card,
.tab-content form {
    border-radius: 0.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* Form Labels */
.form-label {
    font-weight: 600;
}

/* Input Shadow */
input.form-control {
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

/* Tabs Shadow */
.nav-tabs {
    border-bottom: 2px solid #dee2e6;
}
.nav-tabs .nav-link.active {
    background-color: #17007C !important;
    color: white !important;
    font-weight: 600;
}

/* Password List */
#passwordRequirements li.text-success::before {
    content: "✔ ";
    color: green;
}
#passwordRequirements li.text-danger::before {
    content: "✖ ";
    color: red;
}
.btn-theme:disabled {
    background-color: #17007C !important;
    color: #fff !important;
    opacity: 0.6; /* optional: slightly faded to indicate disabled */
    cursor: not-allowed;
}
.form-control, .form-select {
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    border-radius: 0.4rem;
}

</style>
@endsection
