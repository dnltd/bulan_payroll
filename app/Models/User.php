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
        'first_name',
        'middle_name',
        'last_name',
        'role',
        'is_verified',
        'employee_id',
        'profile_picture',
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

    // Relationship to employee record
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Accessor for full name
    public function getFullNameAttribute()
    {
        return $this->first_name 
            . ($this->middle_name ? ' ' . $this->middle_name : '') 
            . ' ' . $this->last_name;
    }
}
