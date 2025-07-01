<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Employee;
use App\Models\SalaryRate;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'dispatchers' => User::whereIn('role', ['admin', 'dispatcher'])->with('employee')->get(),
            'availableEmployees' => Employee::doesntHave('user')->get(),
            'salaryRates' => SalaryRate::all()
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'contact_number' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = Auth::user();
        $user->full_name = $request->name;
        $user->email = $request->email;

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();

            $file->storeAs('profile_pictures', $filename, 'public');

            $user->profile_picture = $filename;
        }

        $user->save();

        if ($user->employee) {
            $user->employee->contact_number = $request->contact_number;
            $user->employee->save();
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function createUser(Request $request)
{
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'email' => 'required|email|unique:users,email',
        'role' => 'required|in:admin,dispatcher'
    ]);

    $defaultPassword = \Str::random(8); // generate random 8-char password

    $employee = Employee::findOrFail($request->employee_id);

    $user = new User();
    $user->full_name = $employee->full_name;
    $user->email = $request->email;
    $user->password = \Hash::make($defaultPassword);
    $user->role = $request->role;
    $user->employee_id = $employee->id;
    $user->is_verified = true;
    $user->save();

    // Pass new account details to view
    return redirect()
        ->back()
        ->with('success', 'Account created.')
        ->with('new_account', [
            'name' => $user->full_name,
            'email' => $user->email,
            'role' => ucfirst($user->role),
            'password' => $defaultPassword
        ]);
}


    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        if ($user->employee) {
            $user->employee->user_id = null;
            $user->employee->save();
        }
        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }

    public function storeSalary(Request $request)
    {
        $request->validate([
            'position' => 'required|string',
            'daily_rate' => 'required|numeric',
            'overtime' => 'required|numeric',
        ]);

        SalaryRate::create($request->only('position', 'daily_rate', 'overtime'));
        return back()->with('success', 'Salary rate added.');
    }

    public function updateSalary(Request $request, $id)
    {
        $request->validate([
            'position' => 'required|string',
            'daily_rate' => 'required|numeric',
            'overtime' => 'required|numeric',
        ]);

        $rate = SalaryRate::findOrFail($id);
        $rate->update($request->only('position', 'daily_rate', 'overtime'));

        return back()->with('success', 'Salary rate updated.');
    }

    public function deleteSalary($id)
    {
        SalaryRate::findOrFail($id)->delete();
        return back()->with('success', 'Salary rate deleted.');
    }
}
