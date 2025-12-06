@extends('layouts.app')

@section('content')

<div class="container py-4"> 
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Dashboard</h2>
        <p class="text-muted small mb-0">Overview of payroll, attendance, and employee statistics</p>
    </div>

    <!-- Modern Real-Time PH Time -->
    <div id="ph-time" class="mt-3 mt-md-0 d-flex align-items-center gap-2">
        <div class="time-badge shadow-sm px-3 py-1 d-flex align-items-center rounded-pill">
            <i class="bi bi-clock me-2 text-light"></i>
            <span id="clock" class="fw-semibold"></span>
            <span class="text-light-50 ms-1 small">(PH)</span>
        </div>
    </div>
</div>

</div>

{{-- Summary Cards --}}
<div class="row mb-4 g-3">
    @php
        // Summary Cards
$summaryCards = [
    ['icon'=>'bi-people-fill','title'=>'Total Employees','value'=>$totalEmployees ?? 0,'url'=>route('admin.employees.index')],
    ['icon'=>'bi-cash-coin','title'=>'Total Payroll','value'=>"₱".number_format($totalPayroll ?? 0,2),'url'=>route('admin.payroll.index')],
    ['icon'=>'bi-dash-circle','title'=>'Total Deductions','value'=>"₱".number_format($totalDeductions ?? 0,2),'url'=>route('admin.deductions.index')],
    ['icon'=>'bi-wallet2','title'=>'Total Net Payroll','value'=>"₱".number_format(max(($totalPayroll ?? 0) - ($totalDeductions ?? 0),0),2),'url'=>route('admin.payroll.index')],
];

    @endphp

    @foreach ($summaryCards as $card)
        <div class="col-6 col-md-3">
            <a href="{{ $card['url'] }}" class="text-decoration-none">
                <div class="card shadow-sm border-0 text-white h-100"
                     style="background: linear-gradient(135deg, #17007C, #3422b5); cursor:pointer;">
                    <div class="card-body text-center p-3">
                        <i class="bi {{ $card['icon'] }} fs-3 opacity-75 mb-2"></i>
                        <h6 class="small text-uppercase mb-1">{{ $card['title'] }}</h6>
                        <h4 class="fw-bold mb-0">{{ $card['value'] }}</h4>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

{{-- Charts Section (Single Row) --}}
<div class="row g-3">
    {{-- Deductions --}}
    <div class="col-lg-4 col-md-12">
        <a href="{{ route('admin.deductions.index') }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 rounded-4 h-100" style="background-color: #f8faff; cursor:pointer;">
                <div class="card-header bg-transparent border-0 text-center py-2">
                    <h6 class="fw-semibold text-secondary small mb-0">Deductions</h6>
                </div>
                <div class="card-body p-2" style="min-height:220px;">
                    <canvas id="deductionsDonut" style="width:100%; height:100%;"></canvas>
                </div>
            </div>
        </a>
    </div>

    {{-- Payroll Distribution --}}
    <div class="col-lg-4 col-md-12">
        <a href="{{ route('admin.payroll.index') }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 rounded-4 h-100" style="background-color: #f8faff; cursor:pointer;">
                <div class="card-header bg-transparent border-0 text-center py-2">
                    <h6 class="fw-semibold text-secondary small mb-0">Payroll Distribution by Role</h6>
                </div>
                <div class="card-body p-2" style="min-height:220px;">
                    <canvas id="payrollDistribution" style="width:100%; height:100%;"></canvas>
                </div>
            </div>
        </a>
    </div>

    {{-- Attendance Overview --}}
    <div class="col-lg-4 col-md-12">
        <a href="{{ route('admin.attendance.index') }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 rounded-4 h-100" style="background-color: #f8faff; cursor:pointer;">
                <div class="card-header bg-transparent border-0 text-center py-2">
                    <h6 class="fw-semibold text-secondary small mb-0">Attendance Overview</h6>
                </div>
                <div class="card-body p-2" style="min-height:220px;">
                    <canvas id="attendanceOverview" style="width:100%; height:100%;"></canvas>
                </div>
            </div>
        </a>
    </div>

    {{-- Upcoming Holidays --}}
    <div class="col-12">
        <a href="{{ route('admin.holidays.index') }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 rounded-4" style="cursor:pointer;">
                <div class="card-header bg-transparent border-0 text-center py-2">
                    <h6 class="fw-semibold text-secondary small mb-0">📅 Upcoming Holidays</h6>
                </div>
                <div class="card-body">
                    @if($recentHolidays && $recentHolidays->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($recentHolidays as $holiday)
                                <li class="list-group-item d-flex justify-content-between align-items-center rounded-3 mb-2 px-3 py-2 shadow-sm border-0 holiday-item">
                                    <span><i class="bi bi-calendar-event text-primary me-2"></i>{{ $holiday->name }}</span>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill">
                                        {{ \Carbon\Carbon::parse($holiday->date)->format('M d, Y') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center small mb-0">No upcoming holidays</p>
                    @endif
                </div>
            </div>
        </a>
    </div>
</div>


{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function createGradient(ctx, colorStart, colorEnd) {
    const gradient = ctx.createLinearGradient(0,0,0,250);
    gradient.addColorStop(0,colorStart);
    gradient.addColorStop(1,colorEnd);
    return gradient;
}

// Generic responsive font size helper
function calcFontSize(chartWidth) {
    // base on width, clamp to [10, 14]
    const size = Math.round(Math.max(10, Math.min(14, chartWidth / 60)));
    return size;
}

function applyResponsiveFonts(chart) {
    if (!chart) return;
    const w = chart.canvas.clientWidth || chart.width || 300;
    const fs = calcFontSize(w);

    // Legend labels
    if (chart.options.plugins && chart.options.plugins.legend && chart.options.plugins.legend.labels) {
        chart.options.plugins.legend.labels.font = chart.options.plugins.legend.labels.font || {};
        chart.options.plugins.legend.labels.font.size = fs;
        chart.options.plugins.legend.labels.padding = Math.max(8, Math.round(fs / 1.2));
    }

    // Tooltip fonts
    if (chart.options.plugins && chart.options.plugins.tooltip) {
        chart.options.plugins.tooltip.titleFont = chart.options.plugins.tooltip.titleFont || {};
        chart.options.plugins.tooltip.titleFont.size = Math.max(11, fs);
        chart.options.plugins.tooltip.bodyFont = chart.options.plugins.tooltip.bodyFont || {};
        chart.options.plugins.tooltip.bodyFont.size = fs;
    }

    // Axis ticks
    if (chart.options.scales) {
        Object.keys(chart.options.scales).forEach(function(scaleKey) {
            const ticks = chart.options.scales[scaleKey].ticks || {};
            ticks.font = ticks.font || {};
            ticks.font.size = fs;
            chart.options.scales[scaleKey].ticks = ticks;
        });
    }

    chart.update();
}

/* -------------------------
   Deductions Donut
   ------------------------- */
const deductionsCtx = document.getElementById('deductionsDonut').getContext('2d');
const deductionLabels = @json(array_keys($deductionsByType ?? []));
const deductionValues = @json(array_values($deductionsByType ?? []));
const deductionsChart = new Chart(deductionsCtx, {
    type:'doughnut',
    data:{
        labels:deductionLabels,
        datasets:[{
            data:deductionValues,
            backgroundColor:[
                createGradient(deductionsCtx,'#17007C','#3422b5'),
                '#fd7e14','#0d6efd','#198754','#6f42c1','#ffc107'
            ],
            hoverOffset:10
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{position:'bottom', labels:{usePointStyle:true}},
            tooltip:{backgroundColor:'rgba(0,0,0,0.75)',titleColor:'#fff',bodyColor:'#fff'}
        }
    }
});

/* -------------------------
   Payroll Distribution (Bar)
   ------------------------- */
const distributionCtx = document.getElementById('payrollDistribution').getContext('2d');
const distLabels = @json(array_keys($payrollDistribution ?? []));
const distValues = @json(array_values($payrollDistribution ?? []));
const distributionChart = new Chart(distributionCtx, {
    type:'bar',
    data:{
        labels:distLabels,
        datasets:[{
            label:'Payroll',
            data:distValues,
            backgroundColor:createGradient(distributionCtx,'#17007C','#3422b5'),
            borderRadius:6
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{display:false},
            tooltip:{enabled:true}
        },
        scales:{
            y:{beginAtZero:true, grid:{color:'rgba(0,0,0,0.05)'}},
            x:{
                grid:{display:false},
                ticks:{autoSkip:true, maxRotation:45, minRotation:0}
            }
        }
    }
});

/* -------------------------
   Attendance Overview (Bar)
   ------------------------- */
const attendanceCtx = document.getElementById('attendanceOverview').getContext('2d');
const attendanceChart = new Chart(attendanceCtx, {
    type:'bar',
    data:{
        labels:['Present','Absent'],
        datasets:[{
            label:'Employees',
            data:[{{ $presentCount ?? 0 }}, {{ $absentCount ?? 0 }}],
            backgroundColor:[
                createGradient(attendanceCtx,'#17007C','#3422b5'),
                createGradient(attendanceCtx,'#dc3545','#f28b92')
            ],
            borderRadius:8
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{legend:{display:false}},
        scales:{
            y:{beginAtZero:true,grid:{color:'rgba(0,0,0,0.05)'}},
            x:{grid:{display:false},ticks:{autoSkip:true}}
        }
    }
});

/* Apply responsive fonts initially and on resize */
function applyAllResponsiveFonts() {
    [deductionsChart, distributionChart, attendanceChart].forEach(applyResponsiveFonts);
}

// run after DOM and after Chart created
window.addEventListener('load', function() {
    applyAllResponsiveFonts();
});

// Recalculate on resize (debounced)
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        applyAllResponsiveFonts();
    }, 200);
});

// Make chart canvases clickable
document.getElementById('deductionsDonut').addEventListener('click', function() {
    window.location.href = "{{ route('admin.deductions.index') }}";
});

document.getElementById('payrollDistribution').addEventListener('click', function() {
    window.location.href = "{{ route('admin.payroll.index') }}";
});

document.getElementById('attendanceOverview').addEventListener('click', function() {
    window.location.href = "{{ route('admin.attendance.index') }}";
});

function updatePhilippineTime() {
    const options = {
        timeZone: "Asia/Manila",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hour12: true
    };
    const now = new Date().toLocaleTimeString("en-US", options);
    document.getElementById("clock").textContent = now;
}

setInterval(updatePhilippineTime, 1000);
updatePhilippineTime();
</script>

{{-- Styles --}}
<style>
.time-badge {
    background: linear-gradient(135deg, #17007C, #3422b5);
    color: #fff;
    font-size: 0.9rem;
    letter-spacing: 0.3px;
    border: 1px solid rgba(255,255,255,0.15);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    animation: glowPulse 2.5s ease-in-out infinite;
}

@keyframes glowPulse {
    0% { box-shadow: 0 0 6px rgba(23, 0, 124, 0.5); }
    50% { box-shadow: 0 0 14px rgba(52, 34, 181, 0.6); }
    100% { box-shadow: 0 0 6px rgba(23, 0, 124, 0.5); }
}

#ph-time {
    user-select: none;
    cursor: default;
}
.card, .holiday-item {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card:hover, .holiday-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

/* Make canvas containers flexible and responsive */
.card-body { padding: 1rem 1.25rem; }

/* ensure chart canvases fill available card-body height */
.card-body > canvas {
    display: block;
    width: 100% !important;
    height: 100% !important;
    max-height: 320px;
}

/* smaller legend dot point style for compactness */
.chartjs-legend li span {
    width: 10px;
    height: 10px;
}

/* holidays badge subtle */
.badge.bg-primary-subtle {
    background-color: rgba(23, 0, 124, 0.1) !important;
}
/* 📱 Mobile Friendly Adjustments */
@media (max-width: 576px) {
    h2.fw-bold { font-size: 1.25rem; }
    p.text-muted { font-size: 0.8rem; }
    .card-header h6 { font-size: 0.8rem; }
    .fw-bold.mb-0 { font-size: 1rem; }
}

</style>
@endsection
