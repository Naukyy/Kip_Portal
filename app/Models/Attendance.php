<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    const STATUS_PENDING    = 'pending';
    const STATUS_HADIR      = 'hadir';
    const STATUS_ALPHA      = 'alpha';
    const STATUS_SAKIT      = 'sakit';
    const STATUS_IZIN       = 'izin';

    const STATUSES = ['pending', 'hadir', 'alpha', 'sakit', 'izin'];

    protected $fillable = [
        'class_meeting_id', 'student_id', 'trainer_id', 'substitute_trainer_id',
        'date', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    // ── Relations ───────────────────────────────
    public function classMeeting()
    {
        return $this->belongsTo(ClassMeeting::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function substituteTrainer()
    {
        return $this->belongsTo(User::class, 'substitute_trainer_id');
    }

    // ── Helpers ─────────────────────────────────
    public function statusLabel(): string
    {
        return match ($this->status) {
            'hadir'  => 'Hadir',
            'alpha'  => 'Alpha',
            'sakit'  => 'Sakit',
            'izin'   => 'Izin',
            default  => 'Pending',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'hadir'  => 'green',
            'alpha'  => 'red',
            'sakit'  => 'orange',
            'izin'   => 'yellow',
            default  => 'gray',
        };
    }
}