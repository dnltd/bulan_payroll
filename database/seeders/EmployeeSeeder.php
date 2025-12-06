<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SalaryRate;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'first_name'     => 'Juan',
                'middle_name'    => 'Dela',
                'last_name'      => 'Cruz',
                'email'          => 'driver@example.com',
                'position'       => 'Driver',
                'address'        => 'Bulan, Sorsogon',
                'contact_number' => '09171234567',
            ],
            [
                'first_name'     => 'Pedro',
                'middle_name'    => 'Santos',
                'last_name'      => 'Reyes',
                'email'          => 'conductor@example.com',
                'position'       => 'Conductor',
                'address'        => 'Bulan, Sorsogon',
                'contact_number' => '09181234567',
            ],
            [
                'first_name'     => 'Maria',
                'middle_name'    => 'Lopez',
                'last_name'      => 'Garcia',
                'email'          => 'dispatcher@example.com',
                'position'       => 'Dispatcher',
                'address'        => 'Bulan, Sorsogon',
                'contact_number' => '09191234567',
            ],
            [
                'first_name'     => 'Jose',
                'middle_name'    => 'Villanueva',
                'last_name'      => 'Cruz',
                'email'          => 'cashier@example.com',
                'position'       => 'Cashier',
                'address'        => 'Bulan, Sorsogon',
                'contact_number' => '09201234567',
            ],
            [
                'first_name'     => 'Ana',
                'middle_name'    => 'Ramos',
                'last_name'      => 'Torres',
                'email'          => 'treasurer@example.com',
                'position'       => 'Treasurer',
                'address'        => 'Bulan, Sorsogon',
                'contact_number' => '09211234567',
            ],
            [
                'first_name'     => 'Liza',
                'middle_name'    => 'Castro',
                'last_name'      => 'Martinez',
                'email'          => 'secretary@example.com',
                'position'       => 'Secretary',
                'address'        => 'Bulan, Sorsogon',
                'contact_number' => '09221234567',
            ],
            [
                'first_name'     => 'Carlos',
                'middle_name'    => 'Domingo',
                'last_name'      => 'Fernandez',
                'email'          => 'gm@example.com',
                'position'       => 'General Manager',
                'address'        => 'Bulan, Sorsogon',
                'contact_number' => '09231234567',
            ],
        ];

        foreach ($employees as $employee) {
            $salaryRate = SalaryRate::where('position', $employee['position'])->first();

            DB::table('employees')->insert([
                'first_name'     => $employee['first_name'],
                'middle_name'    => $employee['middle_name'],
                'last_name'      => $employee['last_name'],
                'email'          => $employee['email'],
                'position'       => $employee['position'],
                'address'        => $employee['address'],
                'contact_number' => $employee['contact_number'],
                'salary_rates_id'=> $salaryRate ? $salaryRate->id : null,
            ]);
        }
    }
}
