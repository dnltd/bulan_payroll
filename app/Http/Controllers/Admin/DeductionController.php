<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deduction;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DeductionController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::all();

        $deductions = Deduction::with('employee')
            ->when($request->type, function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->employee_id, function ($query) use ($request) {
                $query->where('employee_id', $request->employee_id);
            })
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('admin.deductions.index', compact('deductions', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
        ]);

        Deduction::create([
            'employee_id' => $request->employee_id,
            'type'        => $request->type,
            'amount'      => $request->amount,
            'date'        => $request->date,
        ]);

        return back()->with('success', 'Deduction added successfully.');
    }

    public function destroy(Deduction $deduction)
    {
        $deduction->delete();
        return back()->with('success', 'Deduction deleted.');
    }

    public function applySSSDaily()
    {
        $employees = Employee::all();
        $today = Carbon::today()->toDateString();

        foreach ($employees as $employee) {
            $exists = Deduction::where('employee_id', $employee->id)
                ->where('type', 'SSS')
                ->whereDate('date', $today)
                ->exists();

            if (!$exists) {
                Deduction::create([
                    'employee_id' => $employee->id,
                    'type'        => 'SSS',
                    'amount'      => 19.03,
                    'date'        => $today,
                ]);
            }
        }

        return redirect()->back()->with('success', 'SSS deduction of ₱19.03 applied to all employees for today.');
    }

    
    public function exportPDF()
{
    $deductions = Deduction::with('employee')->orderBy('date')->get();
    $pdf = Pdf::loadView('admin.deductions.pdf', [
        'deductions' => $deductions
    ])->setPaper('a4', 'portrait');

    return $pdf->download('deductions.pdf');
}
public function print(Request $request)
{
    $deductions = Deduction::with('employee')
        ->when($request->type, fn($q) => $q->where('type', $request->type))
        ->when($request->employee_id, fn($q) => $q->where('employee_id', $request->employee_id))
        ->get();

    return view('admin.deductions.print', [
        'deductions' => $deductions,
        'date' => now()->format('F d, Y')
    ]);
}


}
