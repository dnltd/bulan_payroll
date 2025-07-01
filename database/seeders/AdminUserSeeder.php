<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalaryRate;
use App\Models\Employee;
use App\Models\User;
use App\Models\Admin;

class AdminUserSeeder extends Seeder
{
    public function run()
    {

// 1. Create or fetch the salary rate for Secretary
$salary = SalaryRate::firstOrCreate(
    ['position' => 'Secretary'],
    ['daily_rate' => 500, 'overtime' => 50]
);

// 2. Create the Employee
$employee = Employee::create([
    'full_name' => 'Danica Letada',
    'position' => 'Secretary',
    'address' => 'Zone 4 Bulan, Sorsogon',
    'contact_number' => '09301607668',
    'salary_rates_id' => $salary->id,
]);

// 3. Create the User
$user = User::create([
    'email' => 'danica.letada@sorsu.edu.ph',
    'password' => bcrypt('password123'),
    'full_name' => 'Danica Letada',
    'role' => 'admin',
    'is_verified' => 1,
]);

// 4. Link to Admin table
Admin::create([
    'user_id' => $user->id,
    'employee_id' => $employee->id,
]);

    }
}
