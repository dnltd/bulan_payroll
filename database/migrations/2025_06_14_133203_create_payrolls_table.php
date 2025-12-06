<?php

// database/migrations/xxxx_xx_xx_create_payrolls_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollsTable extends Migration
{
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('employee_id');
    $table->decimal('gross_salary', 10, 2);
    $table->decimal('deductions', 10, 2)->default(0);
    $table->json('deductions_list')->nullable(); // detailed deductions
    $table->decimal('overtime_pay', 10, 2)->default(0);
    $table->json('holiday_names')->nullable();
    $table->decimal('holiday_pay', 10, 2)->default(0);
    $table->integer('work_days')->default(0);
    $table->integer('overtime_units')->default(0);// keep hours not generic "overtime"
    $table->decimal('net_salary', 10, 2);
    $table->date('date');
    $table->timestamps();

    $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
});

    }

    public function down()
    {
        Schema::dropIfExists('payrolls');
    }
}
