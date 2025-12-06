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
    // Paginate users instead of using get()
    $users = User::with('employee')
                 ->whereIn('role', ['admin', 'dispatcher'])
                 ->orderBy('id', 'desc')
                 ->paginate(10); // 10 per page

    $availableEmployees = Employee::doesntHave('user')->get();
    $salaryRates = SalaryRate::all();

    return view('admin.settings.index', compact('users', 'availableEmployees', 'salaryRates'));
}


    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email' => 'required|email',
            'contact_number' => 'nullable|numeric',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = Auth::user();
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;

        if ($request->hasFile('profile_picture')) {
    // Delete old picture if exists
    if ($user->profile_picture && Storage::disk('public')->exists('profile_pictures/' . $user->profile_picture)) {
        Storage::disk('public')->delete('profile_pictures/' . $user->profile_picture);
    }

    // Upload new picture
    $file = $request->file('profile_picture');
    $filename = time() . '_' . $file->getClientOriginalName();
    $file->storeAs('profile_pictures', $filename, 'public');

    $user->profile_picture = $filename;
    } elseif ($request->remove_picture) {
    // ✅ Remove existing profile picture (if requested)
    if ($user->profile_picture && Storage::disk('public')->exists('profile_pictures/' . $user->profile_picture)) {
        Storage::disk('public')->delete('profile_pictures/' . $user->profile_picture);
    }
    $user->profile_picture = null;
    }

        $user->save();

        if ($user->employee) {
            $user->employee->contact_number = $request->contact_number;
            $user->employee->save();
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Protect admin accounts from deletion
        if ($user->role === 'admin') {
            return back()->with('error', 'Admin accounts cannot be deleted.');
        }

        if ($user->employee) {
            $user->employee->user_id = null;
            $user->employee->save();
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password changed successfully.');
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
