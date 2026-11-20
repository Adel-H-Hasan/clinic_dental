<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('medical_note_id')
                ->unique()
                ->constrained('medical_notes')
                ->restrictOnDelete();

            $table->text('content');
            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_reports');
    }
};
