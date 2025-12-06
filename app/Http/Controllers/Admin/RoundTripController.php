<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoundTrip;
use App\Models\Employee;
use Illuminate\Http\Request;

class RoundTripController extends Controller
{
    public function index(Request $request)
{
    $employees = Employee::all();

    $search     = $request->input('search');
    $employeeId = $request->input('employee_id');
    $position   = $request->input('position');
    // ✅ Use today's date as default if not set
    $date       = $request->input('date', now()->toDateString());

    $roundTrips = RoundTrip::with(['employee', 'holiday'])
        ->when($employeeId, function ($query, $employeeId) {
            $query->where('employee_id', $employeeId);
        })
        ->when(!$employeeId && $search, function ($query) use ($search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$search}%"]);
            });
        })
        ->when($position, function ($query, $position) {
            $query->whereHas('employee', function ($q) use ($position) {
                $q->where('position', $position);
            });
        })
        ->when($date, function ($query, $date) {
            $query->whereDate('date', $date);
        })
        ->orderBy('date', 'desc')
        ->paginate(10)
        ->withQueryString(); // ✅ keep filters when paginating

    return view('admin.round_trip.index', compact('roundTrips', 'employees', 'search', 'employeeId', 'position', 'date'));
}

}
