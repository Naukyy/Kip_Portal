<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->unsignedTinyInteger('min_students');
            $table->unsignedTinyInteger('max_students');
            $table->decimal('rate_senior', 10, 2);
            $table->decimal('rate_junior', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('transaction_categories', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Incentive', 'Deduction']);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_categories');
        Schema::dropIfExists('salary_tiers');
    }
};