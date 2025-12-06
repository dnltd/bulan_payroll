<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispatcher extends Model
{
    use HasFactory;

    protected $table = 'dispatcher';

    protected $fillable = ['user_id', 'employee_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
