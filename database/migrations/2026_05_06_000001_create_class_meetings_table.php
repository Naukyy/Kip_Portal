<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->string('session_time')->nullable(); // e.g. "08:00 - 09:30"
            $table->string('day_name')->nullable();     // e.g. "Senin"
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->time('started_at')->nullable();
            $table->time('ended_at')->nullable();
            $table->timestamps();

            $table->unique(['trainer_id', 'date', 'session_time']);
            $table->index(['trainer_id', 'date']);
        });

        // Add class_meeting_id + update status enum on attendances
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('class_meeting_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('class_meetings')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\ClassMeeting::class);
            $table->dropColumn('class_meeting_id');
        });
        Schema::dropIfExists('class_meetings');
    }
};
