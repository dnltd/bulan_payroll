@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-3">Scan Face for Attendance</h4>
    <div class="card p-3 shadow-sm">
        <video id="video" width="100%" height="300" autoplay></video>
        <canvas id="canvas" style="display:none;"></canvas>
        <input type="hidden" id="captured_face">
        <button onclick="captureAndSend()" class="btn btn-primary mt-3">
            <i class="bi bi-camera"></i> Scan & Submit
        </button>
        <div id="result" class="mt-3"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');

    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            video.srcObject = stream;
        })
        .catch(err => {
            alert("Camera access denied: " + err);
        });

    function captureAndSend() {
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        const imageData = canvas.toDataURL('image/jpeg');

        fetch('http://127.0.0.1:8001/scan', { // Your Python API endpoint
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ image: imageData })
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('result').innerHTML =
                `<div class="alert alert-${data.status === 'success' ? 'success' : 'danger'}">${data.message}</div>`;
        })
        .catch(error => {
            console.error(error);
            alert("Error connecting to face recognition server");
        });
    }
</script>
@endsection
