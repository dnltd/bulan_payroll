@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Employee Attendance – Face Recognition</h2>
            <p class="text-muted small mb-0">Automatically record employee attendance using facial recognition</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('admin.attendance.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm px-3">
                <i class="bi bi-arrow-left-circle me-1"></i> Back to Attendance Records
            </a>
        </div>
    </div>

    {{-- Camera Preview --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center">
            <video id="video" autoplay class="circle-video mx-auto"></video>
            <canvas id="canvas" style="display: none;"></canvas>
            <p id="resultText" class="mt-3 fw-semibold"></p>
        </div>
    </div>

    {{-- Attendance Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-3 table-responsive">
            <table class="table table-bordered table-hover align-middle bg-white deduction-table mb-0" id="attendanceTable">
                <thead style="background-color:#17007C; color:white;">
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $index => $attendance)
                        @php
                            $datePH = \Carbon\Carbon::parse($attendance->date)
                                        ->timezone('Asia/Manila')
                                        ->format('M d, Y');
                            $timeInPH = $attendance->time_in
                                        ? \Carbon\Carbon::createFromFormat('H:i:s', $attendance->time_in, 'Asia/Manila')
                                                ->format('h:i A')
                                        : '-';
                            $timeOutPH = $attendance->time_out
                                        ? \Carbon\Carbon::createFromFormat('H:i:s', $attendance->time_out, 'Asia/Manila')
                                                ->format('h:i A')
                                        : '-';
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $attendance->employee->first_name ?? '' }} {{ $attendance->employee->last_name ?? '' }}</td>
                            <td>{{ $datePH }}</td>
                            <td>{{ $timeInPH }}</td>
                            <td>{{ $timeOutPH }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No attendance records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Styles --}}
<style>
/* Circular camera preview */
.circle-video {
    border-radius: 50%;
    object-fit: cover;
    width: 300px;
    height: 300px;
    border: 5px solid #17007C;
    transition: border-color 0.3s ease;
}
.circle-video.success { border-color: #28a745 !important; }
.circle-video.error { border-color: #dc3545 !important; }

/* Attendance Table using Deduction Table Styles */
.deduction-table {
    width: 100%;
    font-size: 0.85rem;
    border-collapse: collapse;
}
.deduction-table thead th {
    background-color: #17007C !important;
    color: #fff !important;
    font-weight: 600;
    text-align: left;
    padding: 10px;
    border: 1px solid #17007C !important;
    white-space: nowrap;
}
.deduction-table tbody td {
    text-align: left;
    vertical-align: middle;
    padding: 8px 10px;
    border: 1px solid #dee2e6;
}
.deduction-table tbody tr:hover {
    background-color: #eef2ff;
}
</style>

<script>
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const resultText = document.getElementById('resultText');
const scanInterval = 5000; // scan every 5 seconds

// Access webcam
navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => video.srcObject = stream)
    .catch(err => {
        console.error("Webcam error: ", err);
        resultText.innerText = "Webcam access is required.";
    });

// Auto scan
setInterval(scanFace, scanInterval);

// Format time string (HH:MM:SS) to PH time AM/PM
function formatTimePH(timeStr) {
    if (!timeStr) return '-';
    const [h, m, s] = timeStr.split(':').map(Number);
    const hour12 = h % 12 || 12;
    const ampm = h >= 12 ? 'PM' : 'AM';
    return `${hour12.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')} ${ampm}`;
}

async function scanFace() {
    const context = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    const imageData = canvas.toDataURL('image/jpeg');

    try {
        resultText.innerText = "Scanning...";
        video.classList.remove("success", "error");

        const response = await fetch("{{ route('admin.attendance.capture') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ image_data: imageData })
        });

        const data = await response.json();

        if (data.result === "match") {
            resultText.innerText = "✅ Recognized: " + data.message;
            video.classList.add("success");
        } else if (data.result === "already_scanned") {
            resultText.innerText = "⚠️ " + data.message;
            video.classList.add("error");
        } else if (data.result === "no_face") {
            resultText.innerText = "⚠️ No face detected.";
            video.classList.add("error");
        } else if (data.result === "no_match") {
            resultText.innerText = "❌ No match found.";
            video.classList.add("error");
        } else {
            resultText.innerText = "Error: " + data.message;
            video.classList.add("error");
        }

        loadAttendanceTable();

    } catch (error) {
        console.error("Error:", error);
        resultText.innerText = "Error occurred while scanning.";
        video.classList.add("error");
    }
}

async function loadAttendanceTable() {
    try {
        const response = await fetch("{{ route('admin.attendance.today') }}");
        const records = await response.json();
        const tbody = document.querySelector("#attendanceTable tbody");
        tbody.innerHTML = "";

        if (!records.length) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No attendance records yet.</td></tr>`;
            return;
        }

        const todayDate = new Date().toLocaleDateString('en-PH', {
            year: 'numeric', month: 'short', day: 'numeric', timeZone: 'Asia/Manila'
        });

        records.forEach((rec, index) => {
            tbody.innerHTML += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${rec.first_name ?? ''} ${rec.last_name ?? ''}</td>
                    <td>${todayDate}</td>
                    <td>${formatTimePH(rec.time_in)}</td>
                    <td>${formatTimePH(rec.time_out)}</td>
                </tr>
            `;
        });

    } catch (error) {
        console.error("Error loading attendance table:", error);
    }
}

// Refresh every 5 seconds
setInterval(loadAttendanceTable, 1000);
document.addEventListener("DOMContentLoaded", loadAttendanceTable);
</script>
@endsection
