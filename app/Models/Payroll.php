<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'gross_salary',
        'deductions',
        'net_salary',
        'date',
        'overtime_pay',
        'holiday_pay',
        'deductions_list',
        'work_days',
        'start_date',
        'end_date',
        'overtime_units',
        'holiday_names', // ✅ add this field
    ];

    protected $casts = [
        'deductions_list' => 'array',
        'overtime_units' => 'integer',
        'holiday_names' => 'array', // ✅ store as JSON array
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
