<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // Show attendance index page with filters
    public function index(Request $request)
    {
        $query = Attendance::with(['employee','holiday']);

        // Filter by employee ID
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        // Search by employee name
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('employee', function($q) use ($search) {
                $q->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        // Filter by position
        if ($request->position) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('position', $request->position);
            });
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // Default: show today’s attendance
        if (!$request->employee_id && !$request->search && !$request->position && !$request->filled('date')) {
            $query->whereDate('date', Carbon::today('Asia/Manila')->toDateString());
        }

        $attendances = $query->orderBy('date','desc')->paginate(10);
        $employees = Employee::orderBy('first_name')->get();

        return view('admin.attendance.index', compact('attendances','employees'));
    }

    // Show scan face page with today’s attendance
    public function scan()
{
    $today = Carbon::today('Asia/Manila')->toDateString();

    $attendances = Attendance::with('employee')
        ->where('date', $today)
        ->whereHas('employee', function ($q) {
            $q->whereNotIn('position', ['Driver', 'Conductor']);
        })
        ->orderBy('time_in', 'asc')
        ->get();

    return view('admin.attendance.scan', compact('attendances'));
}


    // Capture webcam image and recognize face
    public function capture(Request $request)
{
    try {
        if (!$request->has('image_data')) {
            return response()->json(['result' => 'error', 'message' => 'No image received']);
        }

        // Save image temporarily
        $timestamp = time();
        $filename = "scan_{$timestamp}.jpg";
        $folder = base_path('python_scripts/known_faces');
        if (!file_exists($folder)) mkdir($folder, 0755, true);

        $filepath = $folder . DIRECTORY_SEPARATOR . $filename;

        $imageData = str_replace('data:image/jpeg;base64,', '', $request->image_data);
        $imageData = str_replace(' ', '+', $imageData);
        file_put_contents($filepath, base64_decode($imageData));

        // Run Python script
        $python = "C:\\Users\\danil\\AppData\\Local\\Programs\\Python\\Python310\\python.exe";
        $script = base_path("python_scripts/recognize_face.py");
        $command = escapeshellcmd("$python $script $filepath");
        $output = shell_exec($command);

        // Delete temp file
        if (file_exists($filepath)) unlink($filepath);

        $result = json_decode($output, true);
        if (!$result) {
            return response()->json(['result' => 'error', 'message' => 'Python script error']);
        }

        if ($result['result'] === 'match') {
            $encodingFileName = $result['name'];
            $employee = Employee::where('encoding_file', $encodingFileName)->first();

            if (!$employee) {
                return response()->json(['result' => 'error', 'message' => 'Employee not found']);
            }

            // 🚫 Prevent drivers and conductors from being scanned by admin
            if (in_array($employee->position, ['Driver', 'Conductor'])) {
                return response()->json([
                    'result' => 'error',
                    'message' => "{$employee->first_name} {$employee->last_name} is a {$employee->position} — attendance should be scanned by the dispatcher."
                ]);
            }

            // 🚫 Prevent admin from scanning themselves
            $user = auth()->user();
            if ($user && (
                $employee->user_id === $user->id || 
                strcasecmp($employee->email, $user->email) === 0 // also match by email
            )) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'You cannot scan your own face while logged in as admin.'
                ]);
            }

            // Continue attendance logic
            $today = Carbon::today('Asia/Manila')->toDateString();
            $now = Carbon::now('Asia/Manila');

            $attendance = Attendance::firstOrNew([
                'employee_id' => $employee->id,
                'date' => $today
            ]);

            // Prevent re-scan if shift ended
            if ($attendance && $attendance->time_out) {
                return response()->json([
                    'result' => 'not_allowed',
                    'message' => "{$employee->first_name} {$employee->last_name} has already timed out for today."
                ]);
            }

            // Check if employee has already timed in but not yet timed out
            if ($attendance->time_in && !$attendance->time_out) {
                $timeIn = Carbon::parse($attendance->time_in, 'Asia/Manila');
                $workedMinutes = $timeIn->diffInMinutes($now);

            // 8 hours = 480 minutes
            if ($workedMinutes < 480) {
                $remainingMinutes = 480 - $workedMinutes;
                $remainingHours = floor($remainingMinutes / 60);
                $remainingMins = $remainingMinutes % 60;

        return response()->json([
            'result' => 'too_early',
            'message' => "{$employee->first_name} {$employee->last_name} cannot time out yet. Please complete {$remainingHours} hour(s) and {$remainingMins} minute(s)."
        ]);
    }

    // ✅ Allow time-out if 8 hours or more (overtime allowed)
}


            // Record attendance
            if (!$attendance->time_in) {
                $attendance->time_in = $now->toTimeString();
                $attendance->time_out = null;
            } else {
                $attendance->time_out = $now->toTimeString();
            }

            $attendance->save();

            return response()->json([
                'result' => 'match',
                'message' => "{$employee->first_name} {$employee->last_name}",
                'distance' => $result['distance'] ?? 0
            ]);
        }

        if ($result['result'] === 'no_face') {
            return response()->json(['result' => 'no_face', 'message' => 'No face detected']);
        }

        return response()->json(['result' => 'no_match', 'message' => 'No matching face found']);
    } catch (\Exception $e) {
        return response()->json(['result' => 'error', 'message' => $e->getMessage()]);
    }
}


    // Return today's attendance log in JSON with PH timezone
    public function todayAttendance()
{
    $today = Carbon::today('Asia/Manila')->toDateString();

    $attendance = DB::table('attendances')
        ->join('employees', 'attendances.employee_id', '=', 'employees.id')
        ->whereDate('attendances.date', $today)
        ->whereNotIn('employees.position', ['Driver', 'Conductor']) // ✅ exclude
        ->select(
            'employees.first_name',
            'employees.last_name',
            'attendances.date',
            'attendances.time_in',
            'attendances.time_out',
        )
        ->orderBy('attendances.time_in', 'asc')
        ->get()
        ->map(function ($record) {
            $record->date = Carbon::parse($record->date)->format('Y-m-d');
            $record->time_in = $record->time_in
                ? Carbon::parse($record->time_in)->format('H:i:s')
                : null;
            $record->time_out = $record->time_out
                ? Carbon::parse($record->time_out)->format('H:i:s')
                : null;
            return $record;
        });

    return response()->json($attendance);
}


public function endShift(Request $request)
{
    $employeeId = $request->employee_id;
    $timeOut = $request->time_out;

    if (!$employeeId) {
        return response()->json(['success' => false, 'message' => 'Employee ID missing.']);
    }

    $employee = Employee::find($employeeId);
    if (!$employee) {
        return response()->json(['success' => false, 'message' => 'Employee not found.']);
    }

    $today = Carbon::now('Asia/Manila')->toDateString();
    $attendance = Attendance::where('employee_id', $employeeId)
        ->whereDate('date', $today)
        ->first();

    if (!$attendance || !$attendance->time_in) {
        return response()->json(['success' => false, 'message' => 'No active shift found for this employee.']);
    }

    // ✅ For office staff — require 8 hours minimum
    if (in_array($employee->position, ['General Manager', 'Treasurer', 'Secretary', 'Inspector', 'Dispatcher'])) {
        $timeIn = Carbon::parse($attendance->time_in, 'Asia/Manila');
        $now = Carbon::now('Asia/Manila');

        $diffInSeconds = $timeIn->diffInSeconds($now);
        $hours = floor($diffInSeconds / 3600);
        $minutes = floor(($diffInSeconds % 3600) / 60);
        $seconds = $diffInSeconds % 60;

        if ($hours < 8) {
            return response()->json([
                'success' => false,
                'message' => sprintf(
                    "Cannot end shift yet. Worked %d hour(s), %d minute(s), and %d second(s) — 8 hours required.",
                    $hours, $minutes, $seconds
                )
            ]);
        }
    }

    // ✅ For drivers/conductors — require 2 rounds minimum
    if (in_array($employee->position, ['Driver', 'Conductor'])) {
        $roundsToday = DB::table('round_trips')
            ->where('employee_id', $employeeId)
            ->whereDate('date', $today)
            ->count();

        if ($roundsToday < 2) {
            return response()->json([
                'success' => false,
                'message' => "Cannot end shift yet. Only {$roundsToday} round(s) completed — 2 required."
            ]);
        }
    }

    // ✅ Update Time Out
    $attendance->time_out = $timeOut;
    $attendance->save();

    return response()->json([
        'success' => true,
        'message' => "{$employee->full_name}'s shift has been ended successfully."
    ]);
}


public function getShiftStatus($employeeId)
{
    $employee = Employee::find($employeeId);
    if (!$employee) {
        return response()->json(['can_end_shift' => false, 'reason' => 'Employee not found.']);
    }

    $today = Carbon::now('Asia/Manila')->toDateString();
    $attendance = Attendance::where('employee_id', $employeeId)
        ->whereDate('date', $today)
        ->first();

    if (!$attendance || !$attendance->time_in) {
        return response()->json(['can_end_shift' => false, 'reason' => 'No active shift found for today.']);
    }

    // If already ended
    if ($attendance->time_out) {
        return response()->json(['can_end_shift' => false, 'reason' => 'Shift already ended.']);
    }

    // ✅ For office staff — require 8 hours
    if (in_array($employee->position, ['General Manager', 'Treasurer', 'Secretary', 'Inspector', 'Dispatcher'])) {
        $timeIn = Carbon::parse($attendance->time_in, 'Asia/Manila');
        $now = Carbon::now('Asia/Manila');
        $diffInSeconds = $timeIn->diffInSeconds($now);

        $hours = floor($diffInSeconds / 3600);
        $minutes = floor(($diffInSeconds % 3600) / 60);
        $seconds = $diffInSeconds % 60;

        if ($hours < 8) {
            return response()->json([
                'can_end_shift' => false,
                'reason' => sprintf(
                    "Worked %d hour(s), %d minute(s), %d second(s) — 8 hours required.",
                    $hours, $minutes, $seconds
                )
            ]);
        }

        return response()->json([
            'can_end_shift' => true,
            'reason' => 'Worked 8+ hours, can end shift.'
        ]);
    }

    // ✅ For drivers/conductors — require 2 rounds
    if (in_array($employee->position, ['Driver', 'Conductor'])) {
        $roundsToday = DB::table('round_trips')
            ->where('employee_id', $employeeId)
            ->whereDate('date', $today)
            ->count();

        if ($roundsToday < 2) {
            return response()->json([
                'can_end_shift' => false,
                'reason' => "Completed {$roundsToday} round(s) — 2 required."
            ]);
        }

        return response()->json([
            'can_end_shift' => true,
            'reason' => 'Completed 2+ rounds, can end shift.'
        ]);
    }

    // Default fallback
    return response()->json(['can_end_shift' => true, 'reason' => 'Shift eligible for ending.']);
}




}

