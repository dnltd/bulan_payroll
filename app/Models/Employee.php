<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'position',
        'address',
        'contact_number',
        'salary_rates_id',
        'face_image', // ✅ Add this if you're saving face images
    ];

    // 🔗 Employee belongs to a salary rate
    public function salaryRate()
{
    return $this->belongsTo(SalaryRate::class, 'salary_rates_id', 'id');
}


    // 🔗 Employee has many attendance records
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // 🔗 Employee has many round trips (drivers, conductors)
    public function roundTrips()
    {
        return $this->hasMany(RoundTrip::class);
    }

    // 🔗 Employee has many deductions
    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }

    // 🔗 Employee has many payroll entries
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    // 🔗 Employee has many payslips
    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    // 🔗 Reverse relationship for admin (if applicable)
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    // 🔗 Reverse relationship for dispatcher (if applicable)
    public function dispatcher()
    {
        return $this->hasOne(Dispatcher::class);
    }
    public function user()
{
    return $this->hasOne(User::class, 'employee_id');
}

}
