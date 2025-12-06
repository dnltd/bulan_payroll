<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\RoundTrip;

class DashboardController extends Controller
{
    public function index(Request $request)
{
    $query = RoundTrip::with('employee');

    // 🔍 Search by employee name
    if ($request->filled('search')) {
    $query->whereHas('employee', function ($q) use ($request) {
        $search = $request->search;
        $q->where('first_name', 'like', "%{$search}%")
          ->orWhere('middle_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%");
    });
}


    // ✅ Filter by status (matches your DB structure)
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // 🔍 Filter by position
    if ($request->filled('position')) {
        $query->whereHas('employee', function ($q) use ($request) {
            $q->where('position', $request->position);
        });
    }

    // 🔍 Filter by type (Regular or Overtime)
    if ($request->filled('type')) {
        if ($request->type == 'regular') {
            $query->where('round_number', '<=', 2);
        } elseif ($request->type == 'overtime') {
            $query->where('round_number', '>', 2);
        }
    }

    $roundTrips = $query->orderByDesc('date')->paginate(10)->withQueryString();

    return view('dispatcher.dashboard', compact('roundTrips'));
}




    /**
     * ✅ Update password for dispatcher
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required'],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',      // at least one uppercase
                'regex:/[a-z]/',      // at least one lowercase
                'regex:/[0-9]/',      // at least one digit
                'regex:/[@$!%*#?&]/', // at least one special character
                'confirmed'
            ],
        ], [
            'new_password.min' => 'Password must be at least 8 characters.',
            'new_password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
            'new_password.confirmed' => 'Password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password changed successfully!');
    }
}
