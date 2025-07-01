@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">Add New Employee</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.employees.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" name="full_name" id="full_name" class="form-control" required value="{{ old('full_name') }}">
                </div>

                <div class="mb-3">
                    <label for="position" class="form-label">Position</label>
                    <select name="position" id="position" class="form-select" required>
                        <option value="">-- Select Position --</option>
                        <option value="General Manager">General Manager</option>
                        <option value="Secretary">Secretary</option>
                        <option value="Treasurer">Treasurer</option>
                        <option value="Inspector">Inspector</option>
                        <option value="Dispatcher">Dispatcher</option>
                        <option value="Driver">Driver</option>
                        <option value="Conductor">Conductor</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea name="address" id="address" class="form-control" required>{{ old('address') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="contact_number" class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" id="contact_number" class="form-control" required value="{{ old('contact_number') }}">
                </div>

                <div class="mb-3">
                    <label for="salary_rates_id" class="form-label">Salary Rate</label>
                    <select name="salary_rates_id" id="salary_rates_id" class="form-select" required>
                        <option value="">-- Select Rate --</option>
                        @foreach ($salaryRates as $rate)
                            <option value="{{ $rate->id }}" {{ old('salary_rates_id') == $rate->id ? 'selected' : '' }}>
                                {{ $rate->position }} - ₱{{ number_format($rate->daily_rate, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Capture Face</label>
                <div class="mb-3">
                    <video id="video" width="100%" height="300" autoplay></video>
                    <canvas id="canvas" width="640" height="480" style="display:none;"></canvas>
                    <input type="hidden" name="captured_face" id="captured_face">
                </div>
                <div class="mb-3">
                    <button type="button" onclick="captureImage()" class="btn btn-sm btn-secondary" id="capture-btn">
                        <i class="bi bi-camera"></i> Capture Face
                    </button>
                    <div class="mt-2">
                        <img id="preview" src="" alt="Preview" class="img-thumbnail" style="display:none; max-width:100%;">
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Add Employee & Save Face
        </button>
    </form>
</div>
@endsection


@push('scripts')
<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const preview = document.getElementById('preview');
    const capturedInput = document.getElementById('captured_face');

    navigator.mediaDevices.getUserMedia({ video: true })
        .then((stream) => {
            video.srcObject = stream;
            video.play();
        })
        .catch((err) => {
            alert("Unable to access camera.");
            console.error(err);
        });

    function captureImage() {
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        const dataURL = canvas.toDataURL('image/jpeg');
        capturedInput.value = dataURL;
        preview.src = dataURL;
        preview.style.display = 'block';

        console.log("Captured base64 image:", dataURL.substring(0, 50)); // Just to confirm in console
    }
</script>
@endpush
