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
        Schema::create('doctor_appointment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_type_id')->constrained()->cascadeOnDelete();
            $table->integer('duration_minutes');
            $table->decimal('price',8,2);
            $table->timestamps();

            $table->unique(['doctor_profile_id', 'appointment_type_id'], 'doctor_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_appointment_types');
    }
};
