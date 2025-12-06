<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoundTrip extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'departure',
        'arrival',
        'return',
        'status',
        'round_number',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    public function holiday()
{
    return $this->belongsTo(Holiday::class);
}
}
