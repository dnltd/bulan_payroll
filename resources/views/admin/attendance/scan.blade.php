@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">Face Scan for Attendance</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="mb-3">
        <video id="video" width="100%" height="400" autoplay playsinline class="rounded shadow-sm border"></video>
        <canvas id="canvas" width="640" height="480" class="d-none"></canvas>
    </div>

    <form id="scan-form" method="POST" action="{{ route('admin.attendance.capture') }}" enctype="multipart/form-data">
        @csrf
        <input type="file" name="image" id="image-input" class="d-none" accept="image/*">
        <button type="button" id="capture-btn" class="btn btn-primary">
            <i class="bi bi-camera"></i> Capture and Submit
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const captureBtn = document.getElementById('capture-btn');
    const imageInput = document.getElementById('image-input');
    const form = document.getElementById('scan-form');

    // Access the user's camera
    navigator.mediaDevices.getUserMedia({ video: true })
        .then((stream) => {
            video.srcObject = stream;
        })
        .catch((err) => {
            alert("Unable to access camera.");
            console.error(err);
        });

    captureBtn.addEventListener('click', () => {
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob((blob) => {
            const file = new File([blob], 'face.jpg', { type: 'image/jpeg' });

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            imageInput.files = dataTransfer.files;

            form.submit();
        }, 'image/jpeg');
    });
</script>
@endpush
