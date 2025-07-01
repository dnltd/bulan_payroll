<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Holiday;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::orderBy('date', 'asc')->get();
        return view('admin.holidays.index', compact('holidays'));
    }

    public function create()
    {
        return view('admin.holidays.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'date' => 'required|date|after:today',
    ]);

    Holiday::create([
        'name' => $request->name,
        'date' => $request->date,
    ]);

    return redirect()->route('admin.holidays.index')->with('success', 'Holiday added successfully.');
}

public function update(Request $request, Holiday $holiday)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'date' => 'required|date|after:today|unique:holidays,date,' . $holiday->id,
    ]);

    $holiday->update([
        'name' => $request->name,
        'date' => $request->date,
    ]);

    return redirect()->route('admin.holidays.index')->with('success', 'Holiday updated successfully.');
}


    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return redirect()->route('admin.holidays.index')->with('success', 'Holiday deleted successfully.');
    }
}
