<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('employees', function (Blueprint $table) {
        $table->id();
        $table->string('first_name');
        $table->string('middle_name')->nullable();
        $table->string('last_name');
        $table->string('email')->unique(); // ✅ add email for login
        $table->string('position');
        $table->string('address')->nullable();
        $table->string('contact_number')->nullable();
        $table->unsignedBigInteger('salary_rate_id')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
