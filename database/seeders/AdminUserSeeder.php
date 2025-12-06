<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create employee record for admin
        $employee = Employee::create([
            'first_name'    => 'System',
            'middle_name'   => null,
            'last_name'     => 'Administrator',
            'email'         => 'danica.letada@sorsu.edu.ph',
            'position'      => 'Admin',
            'address'       => 'Bulan, Sorsogon',
            'contact_number'=> '0000000000',
            'salary_rates_id'=> null, 
        ]);

        // Create user record
        $user = User::create([
            'employee_id' => $employee->id,
            'email'       => 'danica.letada@sorsu.edu.ph',
            'password'    => Hash::make('password123'), 
            'role'        => 'admin',
            'is_verified' => true,
            'first_name' => $employee->first_name,
            'middle_name' => $employee->middle_name,
            'last_name' => $employee->last_name,
        ]);

        // Create admin record linked to user
        Admin::create([
            'user_id'     => $user->id,
            'employee_id' => $employee->id,
        ]);
    }
}
