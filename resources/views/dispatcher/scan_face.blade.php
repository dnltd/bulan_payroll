<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatcher Face Scan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-size: 14px;
        }
        .scanner-container {
            max-width: 360px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.15);
            text-align: center;
        }
        video {
            width: 240px;
            height: 240px;
            border-radius: 50%;
            border: 4px solid #17007C;
            object-fit: cover;
            margin-bottom: 12px;
        }
        .top-bar {
            text-align: left;
            margin-bottom: 10px;
        }
        .btn {
            font-size: 13px;
            padding: 6px 10px;
        }
        #result {
            font-size: 13px;
        }
        .banner {
            background-color: #17007C;
            color: white;
            font-weight: 600;
            font-size: 13px;
            border-radius: 8px;
            padding: 6px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body>
<div class="scanner-container">
    <div class="top-bar">
        <a href="{{ route('dispatcher.dashboard') }}" class="btn btn-outline-secondary btn-sm">⬅ Home</a>
    </div>

    <div class="banner">
        🚍 FOR DRIVERS AND CONDUCTORS ONLY (Round-Based)
    </div>

    <video id="video" autoplay playsinline></video>
    <canvas id="canvas" style="display: none;"></canvas>

    <div id="result" class="mt-2"></div>
    <button id="startCameraBtn" class="btn w-100 mt-2" style="background-color:#17007C; color:white; display:none;">📷 Tap to Start Camera</button>
</div>

<script>
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const startCameraBtn = document.getElementById('startCameraBtn');
const resultDiv = document.getElementById('result');
const context = canvas.getContext('2d');

let scanning = true;
let cameraStarted = false;
let currentFacingMode = "environment"; // start with back cam

// Toggle front/back camera
const toggleCameraBtn = document.createElement("button");
toggleCameraBtn.textContent = "🔄 Switch Camera";
toggleCameraBtn.className = "btn btn-outline-primary w-100 mt-2";
document.querySelector(".scanner-container").appendChild(toggleCameraBtn);

toggleCameraBtn.addEventListener("click", () => {
    currentFacingMode = currentFacingMode === "user" ? "environment" : "user";
    stopCamera();
    initCamera(false);
});

// Stop camera
function stopCamera() {
    if (video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
    }
}

// Initialize camera
function initCamera(auto = true) {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: currentFacingMode } })
        .then(stream => {
            video.srcObject = stream;
            cameraStarted = true;
            startCameraBtn.style.display = 'none';
            resultDiv.innerHTML = `<div class="alert alert-info p-1 mb-0">Camera started ✅ (${currentFacingMode === 'user' ? 'Front' : 'Back'})</div>`;
            startAutoScan();
        })
        .catch(err => {
            console.warn("Camera auto-start failed: ", err);
            if (auto) {
                resultDiv.innerHTML = `<div class="alert alert-warning p-1 mb-0">⚠️ Tap below to start camera.</div>`;
                startCameraBtn.style.display = 'block';
            } else {
                resultDiv.innerHTML = `<div class="alert alert-danger p-1 mb-0">Camera access denied. Please allow permissions.</div>`;
            }
        });
}

// Fallback button for iOS/Safari
startCameraBtn.addEventListener('click', () => {
    initCamera(false);
});

// Auto scan every 10 seconds
function autoScan() {
    if (!scanning || !cameraStarted) return;

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    const imageData = canvas.toDataURL('image/jpeg');

    fetch("{{ route('dispatcher.scan.face.capture') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ image_data: imageData })
    })
    .then(res => res.json())
    .then(data => {
        if (data.result === 'match') {
            resultDiv.innerHTML = `
                <div class="alert alert-success p-1 mb-0">
                    ${data.message} <br><small>${data.info}</small>
                </div>`;
        } else if (data.result === 'not_allowed') {
            // Already timed out
            resultDiv.innerHTML = `<div class="alert alert-warning p-1 mb-0">⚠️ ${data.message}</div>`;
        } else if (data.result === 'cooldown') {
            resultDiv.innerHTML = `<div class="alert alert-info p-1 mb-0">${data.message}</div>`;
        } else if (data.result === 'no_face') {
            resultDiv.innerHTML = `<div class="alert alert-warning p-1 mb-0">No face detected, please try again.</div>`;
        } else {
            resultDiv.innerHTML = `<div class="alert alert-warning p-1 mb-0">${data.message}</div>`;
        }
    })
    .catch(err => {
        console.error(err);
        resultDiv.innerHTML = `<div class="alert alert-danger p-1 mb-0">Scan error</div>`;
    });
}

function startAutoScan() {
    setInterval(autoScan, 10000);
}

// Auto initialize on load
window.addEventListener('load', () => {
    initCamera(true);
});
</script>

</body>
</html>
