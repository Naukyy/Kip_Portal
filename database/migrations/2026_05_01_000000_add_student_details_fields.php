<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('parent_name')->nullable()->after('email');
            $table->string('parent_phone')->nullable()->after('parent_name');
            $table->text('address')->nullable()->after('parent_phone');
            $table->date('birth_date')->nullable()->after('address');
            $table->text('notes')->nullable()->after('birth_date');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['parent_name', 'parent_phone', 'address', 'birth_date', 'notes']);
        });
    }
};
