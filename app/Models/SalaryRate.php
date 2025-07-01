<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryRate extends Model
{
    use HasFactory;

    protected $table = 'salary_rates'; 

    protected $fillable = ['position', 'daily_rate', 'overtime'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'salary_rates_id');
    }
}
