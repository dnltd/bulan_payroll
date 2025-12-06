<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Deduction;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function generatePayroll(string $startDate, string $endDate): array
    {
        $employees = Employee::with('salaryRate')->get();
        $payrolls  = [];

        foreach ($employees as $employee) {
            if (!$employee->salaryRate) {
                continue;
            }

            DB::beginTransaction();
            try {
                $salaryRate = $employee->salaryRate;

                $attendances = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date')
                    ->get();

                $uniqueDates = $attendances->pluck('date')->unique();
                $workDays    = $uniqueDates->count(); // ✅ keep total days

                $grossSalary    = 0;
                $holidayPay     = 0;
                $overtimePay    = 0;
                $overtimeUnits  = 0;
                $deductionsList = [];
                $holidayNames   = [];

                // ✅ new: count only regular (qualified) days
                $regularDays = 0;

                foreach ($uniqueDates as $date) {
                    $holiday = Holiday::whereDate('date', $date)->first();
                    $dailyAttendances = $attendances->where('date', $date);

                    $isRegular = false;

                    foreach ($dailyAttendances as $attendance) {
                        if (in_array($employee->position, [
                            'General Manager', 'Secretary', 'Treasurer', 'Inspector', 'Dispatcher', 'Cashier'
                        ])) {
                            // Office-based employees → must work 8 hours
                            if ($attendance->time_in && $attendance->time_out) {
                                $hoursWorked = Carbon::parse($attendance->time_in)
                                    ->diffInHours(Carbon::parse($attendance->time_out));
                                if ($hoursWorked >= 8) {
                                    $isRegular = true;
                                }
                            }
                        } elseif (in_array($employee->position, ['Driver', 'Conductor'])) {
                            // Drivers & Conductors → must complete 2 rounds
                            $rounds = (int)($attendance->rounds ?? 0);
                            if ($rounds >= 2) {
                                $isRegular = true;
                            }
                        }
                    }

                    if ($isRegular) {
                        $regularDays++;

                        if ($holiday) {
                            $holidayPay  += $salaryRate->daily_rate;
                            $grossSalary += $salaryRate->daily_rate * 2; // double pay
                            $holidayNames[] = $holiday->name;
                        } else {
                            $grossSalary += $salaryRate->daily_rate;
                        }

                        // Daily SSS Deduction
                        $sssDailyRate = 19;
                        $exists = Deduction::where('employee_id', $employee->id)
                            ->where('type', 'SSS')
                            ->whereDate('date', $date)
                            ->exists();

                        if (!$exists) {
                            Deduction::create([
                                'employee_id' => $employee->id,
                                'type'        => 'SSS',
                                'amount'      => $sssDailyRate,
                                'date'        => $date,
                            ]);
                        }
                    }
                }

                // ✅ Overtime (same logic)
                foreach ($attendances as $attendance) {
                    if (in_array($employee->position, [
                        'General Manager', 'Secretary', 'Treasurer',
                        'Inspector', 'Dispatcher', 'Cashier'
                    ])) {
                        // Office staff OT by hours > 8
                        if ($attendance->time_in && $attendance->time_out) {
                            $timeIn  = Carbon::parse($attendance->date . ' ' . $attendance->time_in);
                            $timeOut = Carbon::parse($attendance->date . ' ' . $attendance->time_out);

                            $hoursWorked = $timeIn->diffInMinutes($timeOut) / 60;
                            $otHours = max(0, floor($hoursWorked - 8));

                            if ($otHours > 0) {
                                $overtimeUnits += $otHours;
                                $overtimePay   += $otHours * 50;
                                $grossSalary   += $otHours * 50;
                            }
                        }
                    } elseif (in_array($employee->position, ['Driver', 'Conductor'])) {
                        // Drivers & Conductors OT by rounds > 2
                        $rounds = (int)($attendance->rounds ?? 0);
                        if ($rounds > 2) {
                            $extraRounds   = $rounds - 2;
                            $overtimeUnits += $extraRounds;
                            $roundOtPay    = ($salaryRate->daily_rate / 2) * $extraRounds;
                            $overtimePay  += $roundOtPay;
                            $grossSalary  += $roundOtPay;
                        }
                    }
                }

                // ✅ Deductions
                $sssDeductionTotal = Deduction::where('employee_id', $employee->id)
                    ->where('type', 'SSS')
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('amount');

                if ($sssDeductionTotal > 0) {
                    $deductionsList[] = ['type' => 'SSS', 'amount' => $sssDeductionTotal];
                }

                $manualDeductions = Deduction::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->whereNotIn('type', ['SSS', 'Carry-Over'])
                    ->get();

                foreach ($manualDeductions as $deduction) {
                    $deductionsList[] = [
                        'type' => $deduction->type,
                        'amount' => $deduction->amount,
                    ];
                }

                // ✅ Carry-over logic
                $carryOverPrev = Deduction::where('employee_id', $employee->id)
                    ->where('type', 'Carry-Over')
                    ->whereDate('date', '<=', $startDate)
                    ->sum('amount');

                if ($carryOverPrev > 0) {
                    $deductionsList[] = [
                        'type' => 'Carry-Over (Previous Payroll)',
                        'amount' => $carryOverPrev,
                    ];

                    Deduction::where('employee_id', $employee->id)
                        ->where('type', 'Carry-Over')
                        ->whereDate('date', '<=', $startDate)
                        ->delete();
                }

                $totalDeductions = $sssDeductionTotal + $manualDeductions->sum('amount') + $carryOverPrev;
                $netSalary = $grossSalary - $totalDeductions;

                if ($netSalary < 0) {
                    $carryOverAmount = abs($netSalary);
                    $netSalary = 0;

                    Deduction::create([
                        'employee_id' => $employee->id,
                        'type' => 'Carry-Over',
                        'amount' => $carryOverAmount,
                        'date' => Carbon::parse($endDate)->addDay(),
                    ]);

                    $deductionsList[] = [
                        'type' => 'Carry-Over (To Next Payroll)',
                        'amount' => $carryOverAmount,
                    ];
                }

                // ✅ Save payroll (KEEP work_days + ADD regular_days)
                $payroll = Payroll::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'start_date'  => $startDate,
                        'end_date'    => $endDate,
                    ],
                    [
                        'gross_salary'    => $grossSalary,
                        'overtime_pay'    => $overtimePay,
                        'holiday_pay'     => $holidayPay,
                        'deductions'      => $totalDeductions,
                        'net_salary'      => $netSalary,
                        'deductions_list' => $deductionsList,
                        'work_days'       => $workDays,     // keep original
                        'regular_days'    => $regularDays,  
                        'overtime_units'  => $overtimeUnits,
                        'holiday_names'   => $holidayNames,
                    ]
                );

                $payroll->deductions_list = $deductionsList;
                $payroll->holiday_names   = $holidayNames;
                $payrolls[] = $payroll;

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }

        return $payrolls;
    }
}
