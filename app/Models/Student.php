<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'name', 'periode', 'schedule',
        'session_time', 'phone', 'email', 'trainer_id', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Presensi di bulan tertentu
    public function attendancesInMonth(int $month, int $year)
    {
        return $this->attendances()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->keyBy(fn($a) => $a->date->day); // indexed by day number
    }
}