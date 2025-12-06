<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\RoundTrip;
use App\Models\Attendance;
use Carbon\Carbon;

class ScanController extends Controller
{
    /**
     * Show scan page for dispatcher.
     */
    public function scanFace()
    {
        $roundTrips = RoundTrip::with('employee')
            ->whereDate('date', Carbon::today())
            ->orderBy('id', 'desc')
            ->get();

        return view('dispatcher.scan_face', compact('roundTrips'));
    }

    /**
     * Capture face, run recognition, and log round trips + attendance (time_in only).
     */
    public function capture(Request $request)
    {
        try {
            if (!$request->has('image_data')) {
                return response()->json(['result' => 'error', 'message' => 'No image received']);
            }

            // Save temp image
            $timestamp = time();
            $filename = "scan_{$timestamp}.jpg";
            $folder = base_path('python_scripts/temp_scans');

            if (!file_exists($folder)) mkdir($folder, 0755, true);
            $filepath = $folder . DIRECTORY_SEPARATOR . $filename;

            $imageData = str_replace('data:image/jpeg;base64,', '', $request->image_data);
            $imageData = str_replace(' ', '+', $imageData);
            file_put_contents($filepath, base64_decode($imageData));

            // Run Python recognition
            $python = "C:\\Users\\danil\\AppData\\Local\\Programs\\Python\\Python310\\python.exe";
            $script = base_path("python_scripts/recognize_face.py");
            $command = escapeshellcmd("$python $script $filepath");
            $output = shell_exec($command);

            if (file_exists($filepath)) unlink($filepath);

            $result = json_decode($output, true);
            if (!$result) {
                return response()->json(['result' => 'error', 'message' => 'Python script error or invalid output']);
            }

            if ($result['result'] === 'match') {
                $encodingFileName = $result['name'];
                $employee = Employee::where('encoding_file', $encodingFileName)->first();

                if (!$employee) {
                    return response()->json(['result' => 'error', 'message' => 'Employee not found']);
                }

                $position = strtolower($employee->position);
                if (!in_array($position, ['driver', 'conductor'])) {
                    return response()->json([
                        'result' => 'not_allowed',
                        'message' => "{$employee->first_name} {$employee->last_name} is a {$employee->position}. Dispatcher can only scan Drivers and Conductors."
                    ]);
                }

                $today = Carbon::today('Asia/Manila')->toDateString();
                $now = Carbon::now('Asia/Manila');
                $message = '';

                // ✅ Attendance (only set time_in once per day)
                $attendance = Attendance::firstOrNew([
                    'employee_id' => $employee->id,
                    'date' => $today
                ]);

                if (!$attendance->time_in) {
                    $attendance->time_in = $now->format('H:i:s');
                    $attendance->save();
                    $message = "{$employee->first_name} {$employee->last_name} Time In recorded.";
                }

                // 🚫 Prevent scanning after shift ended
if ($attendance->shift_ended || $attendance->time_out) {
    return response()->json([
        'result' => 'not_allowed',
        'message' => "{$employee->first_name} {$employee->last_name} has already ended their shift."
    ]);
}

                // 🚫 Prevent rapid re-scan
                $lastRound = RoundTrip::where('employee_id', $employee->id)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($lastRound && $lastRound->updated_at) {
                    $lastScanTime = Carbon::parse($lastRound->updated_at);
                    if ($lastScanTime->diffInMinutes($now) < 30) {
                        $minutesLeft = 30 - $lastScanTime->diffInMinutes($now);
                        return response()->json([
                            'result' => 'cooldown',
                            'message' => "{$employee->first_name} {$employee->last_name} scanned recently. Please wait {$minutesLeft} minute(s)."
                        ]);
                    }
                }

                // ✅ Round Trip Logic (3 scans per round)
                
$round = RoundTrip::where('employee_id', $employee->id)
    ->whereDate('date', $today)
    ->latest('id')
    ->first();

if (!$round) {
    // First time scanning today → create round 1 but don't increment yet
    $roundNumber = 1;
    $round = RoundTrip::create([
        'employee_id' => $employee->id,
        'date' => $today,
        'departure' => $now->format('H:i:s'),
        'round_number' => $roundNumber,
        'status' => 'Departed',
    ]);
    $message .= " Round {$roundNumber} departure logged.";
} elseif ($round->departure && !$round->arrival) {
    // Record arrival
    $round->update([
        'arrival' => $now->format('H:i:s'),
        'status' => 'Arrived'
    ]);
    $message .= " Round {$round->round_number} arrival logged.";
} elseif ($round->departure && $round->arrival && !$round->return) {
    // Record return → complete the round here
    $round->update([
        'return' => $now->format('H:i:s'),
        'status' => 'Completed'
    ]);
    $message .= " Round {$round->round_number} return logged (✅ Round Completed).";
} elseif ($round->departure && $round->arrival && $round->return) {
    // Start new round only AFTER a round has been completed
    $newRoundNumber = $round->round_number + 1;
    $newRound = RoundTrip::create([
        'employee_id' => $employee->id,
        'date' => $today,
        'departure' => $now->format('H:i:s'),
        'round_number' => $newRoundNumber,
        'status' => 'Departed',
    ]);
    $message .= " New Round {$newRoundNumber} departure logged.";
}


                // ✅ Check if they can end shift (after 2 completed rounds)
                $completedRounds = RoundTrip::where('employee_id', $employee->id)
                    ->whereDate('date', $today)
                    ->where('status', 'Completed')
                    ->count();

                $canEndShift = $completedRounds >= 2;

                return response()->json([
                    'result' => 'match',
                    'message' => "{$employee->first_name} {$employee->last_name}",
                    'info' => $message,
                    'employee_id' => $employee->id,
                    'can_end_shift' => $canEndShift,
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

    /**
     * End shift — logs attendance time_out and marks shift_ended = true.
     */
    public function endShift(Request $request)
    {
        $employeeId = $request->employee_id;
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return response()->json(['status' => 'error', 'message' => 'Employee not found.']);
        }

        $today = Carbon::today('Asia/Manila');
        $completedRounds = RoundTrip::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->where('status', 'Completed')
            ->count();

        if ($completedRounds < 2) {
            return response()->json([
                'status' => 'error',
                'message' => "Cannot end shift. {$employee->first_name} has only completed {$completedRounds} rounds."
            ]);
        }

        $now = Carbon::now('Asia/Manila');
        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            ['time_in' => $now->format('H:i:s')]
        );

        $attendance->update([
            'time_out' => $now->format('H:i:s'),
            'shift_ended' => true
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Shift ended for {$employee->first_name} {$employee->last_name}."
        ]);
    }
}
