<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\SalaryRate;
use App\Models\User;
use App\Models\Admin;
use App\Models\Dispatcher;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Mail\SendCredentials;
use Illuminate\Support\Facades\Mail;

class EmployeeController extends Controller
{
    public function index(Request $request)
{
    $search = $request->input('search');
    $employeeId = $request->input('employee_id');
    $position = $request->input('position'); // ✅ added

    $employees = Employee::with(['salaryRate', 'user'])
        ->when($employeeId, function ($query, $employeeId) {
            $query->where('id', $employeeId);
        })
        ->when($position, function ($query, $position) {
            $query->where('position', $position); // ✅ filter by position
        })
        ->when(!$employeeId && !$position && $search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('middle_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('position', 'like', "%$search%");
            });
        })
        ->orderBy('last_name')
        ->paginate(10)
        ->withQueryString(); // ✅ keep filters on pagination

    $salaryRates = SalaryRate::all();

    return view('admin.employees.index', compact('employees', 'salaryRates', 'search', 'employeeId', 'position'));
}



    public function exportPDF()
    {
        $employees = Employee::with('salaryRate')->get();
        $pdf = Pdf::loadView('admin.employees.pdf', compact('employees'));
        return $pdf->download('employees_report.pdf');
    }

    public function print()
    {
        $employees = Employee::with('salaryRate')->get();
        return view('admin.employees.print', compact('employees'));
    }
    public function store(Request $request)
{
    $request->validate([
    'first_name'      => 'required|string|max:255',
    'middle_name'     => 'nullable|string|max:255',
    'last_name'       => 'required|string|max:255',
    'email'           => 'required|email|unique:employees,email',
    'address' => 'required|string|max:255',
    'contact_number' => ['required','regex:/^(09|\+639)\d{9}$/'], // PH mobile format
    'salary_rates_id' => 'required|exists:salary_rates,id',
    'captured_face'   => 'required|string', // base64 image
],[
        'contact_number.regex' => 'Enter a valid Philippine mobile number (09xxxxxxxxx or +639xxxxxxxxx)',
    ]);

// Check for duplicate full name
$middle = $request->middle_name ?: null;
$existingEmployee = Employee::where('first_name', $request->first_name)
    ->where('middle_name', $middle)
    ->where('last_name', $request->last_name)
    ->first();

if ($existingEmployee) {
    if ($request->expectsJson()) {
        return response()->json([
            'success' => false,
            'message' => 'An employee with the same full name already exists.'
        ]);
    } else {
        return redirect()->back()
            ->withInput()
            ->withErrors(['duplicate' => 'An employee with the same full name already exists.']);
    }
}



    try {
        // Prepare full name for filename
        $fullName = trim($request->first_name . ' ' . ($request->middle_name ?? '') . ' ' . $request->last_name);
        $filename = preg_replace('/\s+/', '_', $fullName) . '_' . time() . '.jpg';
        $folder = base_path('python_scripts/known_faces');

        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }

        // Save face image
        $imageData = str_replace('data:image/jpeg;base64,', '', $request->captured_face);
        $imageData = str_replace(' ', '+', $imageData);
        file_put_contents($folder . DIRECTORY_SEPARATOR . $filename, base64_decode($imageData));

        // Get salary rate
        $salaryRate = SalaryRate::findOrFail($request->salary_rates_id);

        // Create employee
        $employee = Employee::create([
            'first_name'      => $request->first_name,
            'middle_name'     => $request->middle_name,
            'last_name'       => $request->last_name,
            'email'           => $request->email,
            'position'        => $salaryRate->position,
            'address'         => $request->address,
            'contact_number'  => $request->contact_number,
            'salary_rates_id' => $request->salary_rates_id,
            'encoding_file'   => pathinfo($filename, PATHINFO_FILENAME)
        ]);

        // If Dispatcher, create user account + send email
        if (strtolower($salaryRate->position) === 'dispatcher') {
            $randomPassword = Str::random(10);

            $user = User::create([
                'first_name'  => $employee->first_name,
                'middle_name' => $employee->middle_name ?? '',
                'last_name'   => $employee->last_name,
                'email'       => $employee->email,
                'password'    => Hash::make($randomPassword),
                'role'        => 'dispatcher',
                'employee_id' => $employee->id,
                'is_verified' => true,
            ]);

            Dispatcher::create([
                'user_id'     => $user->id,
                'employee_id' => $employee->id,
            ]);

            // Send credentials via email
            Mail::to($user->email)->send(new SendCredentials([
                'email'    => $user->email,
                'password' => $randomPassword,
                'role'     => 'dispatcher'
            ]));

            return response()->json([
                'success' => true,
                'message' => "Dispatcher {$employee->first_name} added successfully. Credentials have been sent to their email."
            ]);
        }

        // Non-dispatcher success
        if ($request->expectsJson()) {
    return response()->json([
        'success' => true,
        'message' => "Employee {$employee->first_name} added successfully"
    ]);
} else {
    return redirect()->route('admin.employees.index')
                     ->with('success', "Employee {$employee->first_name} added successfully");
}


    } catch (\Exception $e) {
    if ($request->expectsJson()) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } else {
        return redirect()->back()->withInput()
            ->withErrors(['error' => $e->getMessage()]);
    }
}
}


    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $salaryRates = SalaryRate::all();
        return view('admin.employees.edit', compact('employee','salaryRates'));
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'first_name'      => 'required|string|max:255',
        'middle_name'     => 'nullable|string|max:50',
        'last_name'       => 'required|string|max:255',
        'email'           => 'required|email|unique:employees,email,'.$id,
        'address'         => 'required|string|max:255',
        'contact_number'  => ['required','regex:/^(09|\+639)\d{9}$/'],
        'salary_rates_id' => 'required|exists:salary_rates,id',
        'captured_face'   => 'nullable|string', // new face optional
    ],[
        'contact_number.regex' => 'Enter a valid Philippine mobile number (09xxxxxxxxx or +639xxxxxxxxx)',
    ]);

    $employee = Employee::findOrFail($id);
    $salaryRate = SalaryRate::findOrFail($request->salary_rates_id);

    // Update employee details
    $employee->update([
        'first_name'      => $request->first_name,
        'middle_name'     => $request->middle_name,
        'last_name'       => $request->last_name,
        'email'           => $request->email,
        'position'        => $salaryRate->position,
        'address'         => $request->address,
        'contact_number'  => $request->contact_number,
        'salary_rates_id' => $request->salary_rates_id,
    ]);

    // Update user account if exists
    if($employee->user){
        $employee->user->update([
            'email'       => $employee->email,
            'first_name'  => $employee->first_name,
            'middle_name' => $employee->middle_name ?? '',
            'last_name'   => $employee->last_name,
            'role'        => strtolower($salaryRate->position) === 'dispatcher' ? 'dispatcher' : $employee->user->role,
        ]);
    }

    // Handle updating the face image if a new one is provided
    if ($request->filled('captured_face')) {
        $folder = base_path('python_scripts/known_faces');

        // Delete old face file if exists
        if ($employee->encoding_file) {
            $oldImagePath = $folder . DIRECTORY_SEPARATOR . $employee->encoding_file . '.jpg';
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        // Save new face image
        $fullName = trim($employee->first_name . ' ' . ($employee->middle_name ?? '') . ' ' . $employee->last_name);
        $filename = preg_replace('/\s+/', '_', $fullName) . '_' . time() . '.jpg';
        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }
        $imageData = str_replace('data:image/jpeg;base64,', '', $request->captured_face);
        $imageData = str_replace(' ', '+', $imageData);
        file_put_contents($folder . DIRECTORY_SEPARATOR . $filename, base64_decode($imageData));

        // Update encoding_file in employee
        $employee->update([
            'encoding_file' => pathinfo($filename, PATHINFO_FILENAME)
        ]);
    }

    return redirect()->route('admin.employees.index')->with('success','Employee updated successfully.');
}

public function checkDuplicate(Request $request)
{
    $first_name = trim($request->first_name);
    $middle_name = trim($request->middle_name);
    $last_name = trim($request->last_name);
    $email = trim($request->email);

    // Default response
    $response = [
        'duplicateName' => false,
        'duplicateEmail' => false,
    ];

    // ✅ Check for duplicate email
    if ($email) {
        $emailExists = Employee::where('email', $email)->exists();
        $response['duplicateEmail'] = $emailExists;
    }

    // ✅ Check for duplicate full name (handle optional middle name)
    if ($first_name && $last_name) {
        $query = Employee::where('first_name', $first_name)
                        ->where('last_name', $last_name);

        if ($middle_name) {
            $query->where('middle_name', $middle_name);
        }

        $nameExists = $query->exists();
        $response['duplicateName'] = $nameExists;
    }

    return response()->json($response);
}


    public function destroy($id)
{
    $employee = Employee::findOrFail($id);

    // Delete known_faces image if exists
    if ($employee->encoding_file) {
        $imagePath = base_path('python_scripts/known_faces/' . $employee->encoding_file . '.jpg');
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete user if exists
    if ($employee->user) {
        $employee->user->delete();
    }

    // Delete employee
    $employee->delete();

    return response()->json(['success' => true]);
}





    public function makeAdmin(Request $request, $id)
    {
        $request->validate([
            'admin_password' => 'required|string'
        ]);

        $employee = Employee::findOrFail($id);
        $currentUser = auth()->user();

        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'No authenticated user.']);
        }

        if (!Hash::check($request->admin_password, $currentUser->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid admin password.']);
        }

        if ($employee->user && $employee->user->role === 'admin') {
            return response()->json(['success' => false, 'message' => 'Already an admin.']);
        }

        $randomPassword = Str::random(10);

        if (!$employee->user) {
            $user = User::create([
                'first_name'  => $employee->first_name,
                'middle_name' => $employee->middle_name ?? '',
                'last_name'   => $employee->last_name,
                'email'       => $employee->email,
                'password'    => Hash::make($randomPassword),
                'role'        => 'admin',
                'employee_id' => $employee->id,
                'is_verified' => true,
            ]);

            Admin::create([
                'user_id' => $user->id,
                'employee_id' => $employee->id,
            ]);
        } else {
            $employee->user->update([
                'role'     => 'admin',
                'password' => Hash::make($randomPassword)
            ]);

            Admin::firstOrCreate([
                'user_id' => $employee->user->id,
                'employee_id' => $employee->id,
            ]);
        }

        // Send credentials via email
try {
    Mail::to($employee->email)->send(new SendCredentials([
        'email'    => $employee->email,
        'password' => $randomPassword,
        'role'     => 'admin'
    ]));
} catch (\Exception $e) {
    return response()->json([
        'success' => false,
        'message' => 'Admin promoted, but email sending failed: ' . $e->getMessage(),
    ]);
}

return response()->json([
    'success' => true,
    'message' => 'Employee promoted to admin successfully. Credentials sent to email.',
    'credentials' => [
        'email'    => $employee->email,
        'password' => $randomPassword
    ]
]);

    }
}
