<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\SalaryRate;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('salaryRate');

        if ($request->has('search')) {
            $query->where('full_name', 'like', '%' . $request->search . '%');
        }

        $employees = $query->paginate(10);

        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        $salaryRates = SalaryRate::all();
        return view('admin.employees.create', compact('salaryRates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'position' => 'required',
            'address' => 'required',
            'contact_number' => 'required',
            'salary_rates_id' => 'required|exists:salary_rates,id',
            'captured_face' => 'required',
        ]);

        $employee = Employee::create($request->only([
            'full_name', 'position', 'address', 'contact_number', 'salary_rates_id'
        ]));

        $this->saveFaceImage($request->captured_face, $employee->full_name);
        $this->encodeFace($employee->full_name);

        return redirect()->route('admin.employees.index')->with('success', 'Employee added and face encoded!');
    }

    public function edit($id)
{
    $employee = Employee::findOrFail($id);
    $salaryRates = SalaryRate::all();
    return view('admin.employees.edit', compact('employee', 'salaryRates'));
}

public function update(Request $request, $id)
{
    $request->validate([
        'full_name' => 'required',
        'position' => 'required',
        'address' => 'required',
        'contact_number' => 'required',
        'salary_rates_id' => 'required|exists:salary_rates,id',
    ]);

    $employee = Employee::findOrFail($id);
    $employee->update($request->only([
        'full_name', 'position', 'address', 'contact_number', 'salary_rates_id'
    ]));

    return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully!');
}

public function destroy($id)
{
    $employee = Employee::findOrFail($id);

    // Optionally delete the image
    $imagePath = public_path('faces/' . $employee->full_name . '.jpg');
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }

    $employee->delete();

    return redirect()->route('admin.employees.index')->with('success', 'Employee deleted successfully!');
}

    private function saveFaceImage($base64, $filename)
    {
        $image = str_replace('data:image/jpeg;base64,', '', $base64);
        $image = str_replace(' ', '+', $image);
        $imagePath = public_path('faces/' . $filename . '.jpg');

        if (!file_exists(public_path('faces'))) {
            mkdir(public_path('faces'), 0777, true);
        }

        file_put_contents($imagePath, base64_decode($image));
    }

    private function encodeFace($employeeName)
    {
        $process = new Process([
            base_path('face_env/Scripts/python.exe'),
            base_path('python_scripts/capture_and_encode.py'),
            $employeeName
        ]);

        $process->setEnv(['PYTHONHASHSEED' => '0']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
