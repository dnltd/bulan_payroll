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
        Schema::create('round_trips', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
    $table->date('date');
    $table->time('departure')->nullable();
    $table->time('arrival')->nullable();
    $table->time('return')->nullable();
    $table->string('status'); // e.g., Completed, Pending
    $table->integer('round_number');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('round_trips');
    }
};
