<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'email',
        'password',
        'full_name',
        'role',
        'is_verified',
        'employee_id', // ✅ Include this
    ];

    protected $hidden = ['password'];

    // Relationship to admin record
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    // Relationship to dispatcher record
    public function dispatcher()
    {
        return $this->hasOne(Dispatcher::class);
    }

    // ✅ Link user to the employee table
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
