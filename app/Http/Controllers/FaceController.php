<?php
// app/Http/Controllers/FaceController.php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class FaceController extends Controller
{
    public function scan()
    {
        $process = new Process(['python', 'python_scripts/scan_face_for_attendance.py']);
        $process->run();

        $resultPath = base_path('scan_result.txt');
        if (file_exists($resultPath)) {
            $data = explode(',', file_get_contents($resultPath));
            $employeeId = trim($data[0]);
            $timestamp = trim($data[1]);

            // You can save attendance to DB here
            return back()->with('success', "Face matched: $employeeId at $timestamp");
        }

        return back()->with('error', 'No match found.');
    }
}
