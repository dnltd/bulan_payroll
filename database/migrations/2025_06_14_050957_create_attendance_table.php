<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
    $table->date('date');
    $table->time('time_in')->nullable();
    $table->time('time_out')->nullable();
    $table->integer('rounds')->nullable();
    $table->foreignId('holiday_id')->nullable()->constrained('holidays')->onDelete('set null');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
