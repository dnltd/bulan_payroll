<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoundTrip;
use App\Models\Employee;

class RoundTripController extends Controller
{
    public function index(Request $request)
    {
        $query = RoundTrip::with('employee')->orderBy('date', 'desc');

        if ($request->filled('search')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $roundTrips = $query->paginate(10);

        return view('admin.round_trip.index', compact('roundTrips'));
    }
}
