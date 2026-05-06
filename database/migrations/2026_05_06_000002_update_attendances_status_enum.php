<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For SQLite (used in this project), we recreate the column approach won't work
        // Instead we use DB::statement to alter or just add new status values
        // Since SQLite doesn't support ALTER COLUMN, we rename the old status and add a new one
        // The cleanest way: use a string column to support new statuses

        // Check if we're using SQLite
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: we can't change enum, but we can update data and use CHECK constraint
            // Just modify existing data to map old -> new status labels
            DB::statement("UPDATE attendances SET status = 'hadir' WHERE status = 'Attend'");
            DB::statement("UPDATE attendances SET status = 'izin' WHERE status = 'Permission'");
            DB::statement("UPDATE attendances SET status = 'alpha' WHERE status = 'Absent'");

            // SQLite doesn't enforce enum so the string values will just work
            // We'll add a new 'sakit' option via application logic
        } else {
            // MySQL: alter the enum column
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('pending','hadir','alpha','sakit','izin') DEFAULT 'pending'");
            DB::statement("UPDATE attendances SET status = 'hadir' WHERE status = 'Attend'");
            DB::statement("UPDATE attendances SET status = 'izin' WHERE status = 'Permission'");
            DB::statement("UPDATE attendances SET status = 'alpha' WHERE status = 'Absent'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver !== 'sqlite') {
            DB::statement("UPDATE attendances SET status = 'Attend' WHERE status = 'hadir'");
            DB::statement("UPDATE attendances SET status = 'Permission' WHERE status = 'izin'");
            DB::statement("UPDATE attendances SET status = 'Absent' WHERE status = 'alpha'");
            DB::statement("UPDATE attendances SET status = 'Attend' WHERE status IN ('sakit','pending')");
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('Attend','Permission','Absent') DEFAULT 'Attend'");
        }
    }
};
