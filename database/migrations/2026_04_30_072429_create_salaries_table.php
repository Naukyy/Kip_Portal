<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('base_earnings', 12, 2)->default(0);
            $table->decimal('total_incentives', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_take_home', 12, 2)->default(0);
            $table->enum('status', ['Draft', 'Finalized'])->default('Draft');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'month', 'year']);
            $table->index(['month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};