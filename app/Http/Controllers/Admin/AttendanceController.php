<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Deduction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('employee')->latest()->paginate(10);
        return view('admin.attendance.index', compact('attendances'));
    }

    public function scan()
    {
        return view('admin.attendance.scan');
    }

    public function capture(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg'
        ]);

        // Save uploaded image temporarily
        $path = $request->file('image')->store('temp_attendance');
        $imagePath = storage_path('app/' . $path);
        $pythonScript = base_path('python_scripts/scan_face_for_attendance.py');

        $process = new Process(['python', $pythonScript, $imagePath]);
        $process->run();

        // Clean up uploaded image
        Storage::delete($path);

        if (!$process->isSuccessful()) {
            return back()->with('error', 'Face scan failed.');
        }

        $output = trim($process->getOutput());

        if ($output === 'NO_MATCH') {
            return back()->with('error', 'No matching face found.');
        }

        list($employeeId, $timestamp) = explode(',', $output);
        $today = Carbon::today();

        // ✅ Apply daily SSS deduction if not already applied
        $this->applySSSDailyIfNotApplied();

        // ✅ Save attendance
        $attendance = Attendance::firstOrNew([
            'employee_id' => $employeeId,
            'date' => $today,
        ]);

        if (is_null($attendance->time_in)) {
            $attendance->time_in = Carbon::now();
            $message = 'Time In recorded.';
        } elseif (is_null($attendance->time_out)) {
            $attendance->time_out = Carbon::now();
            $message = 'Time Out recorded.';
        } else {
            $message = 'Already timed in and out today.';
        }

        $attendance->save();

        return back()->with('success', $message);
    }

    // ✅ Auto apply SSS (₱19.03 daily) if not yet applied today
    private function applySSSDailyIfNotApplied()
    {
        $today = now()->toDateString();
        $employees = Employee::all();

        foreach ($employees as $employee) {
            $exists = Deduction::where('employee_id', $employee->id)
                ->where('type', 'SSS')
                ->whereDate('date', $today)
                ->exists();

            if (!$exists) {
                Deduction::create([
                    'employee_id' => $employee->id,
                    'type' => 'SSS',
                    'amount' => 19.03,
                    'date' => $today,
                ]);
            }
        }
    }
}
