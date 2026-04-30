<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('trainer_id')->constrained('users');
            $table->foreignId('substitute_trainer_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->date('date');
            $table->enum('status', ['Attend', 'Permission', 'Absent'])->default('Attend');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'date']);
            $table->index(['trainer_id', 'date']);
            $table->index(['substitute_trainer_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};