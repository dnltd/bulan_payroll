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
        Schema::create('deductions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
    $table->string('type'); // e.g., SSS, Damage, Advance
    $table->decimal('amount', 10, 2);
    $table->date('date');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
