@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-4 fw-bold">Admin Settings</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <ul class="nav nav-tabs mb-4" id="settingsTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#profile">Profile Management</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#accounts">Account Management</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#salary">Salary Rate Management</a></li>
    </ul>

    <div class="tab-content bg-white p-3 rounded shadow-sm">

        {{-- PROFILE MANAGEMENT --}}
        <div class="tab-pane fade show active" id="profile">
            <form method="POST" action="{{ route('admin.settings.profile') }}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Full Name</label>
                        <input type="text" name="name" value="{{ Auth::user()->full_name }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>Email</label>
                        <input type="email" name="email" value="{{ Auth::user()->email }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" value="{{ Auth::user()->employee->contact_number ?? '' }}" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Upload Profile Picture</label>
                        <input type="file" name="profile_picture" class="form-control" onchange="previewProfile(this)">
                    </div>
                    <div class="col-md-4">
                        <label>Preview</label><br>
                        <img id="profilePreview" src="{{ asset('storage/profile_pictures/' . Auth::user()->profile_picture) }}" 
                             alt="Preview" style="max-height: 120px; max-width: 120px;" class="border rounded">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>

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
    </div>
</div>
@endsection
