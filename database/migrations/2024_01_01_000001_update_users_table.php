<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add whatsapp column and make employee_code required
        Schema::table('users', function (Blueprint $table) {
            // Add whatsapp column after phone
            $table->string('whatsapp')->nullable()->after('phone');
            
            // Make employee_code required (change from nullable)
            $table->string('employee_code')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('whatsapp');
            $table->string('employee_code')->nullable()->change();
        });
    }
};
