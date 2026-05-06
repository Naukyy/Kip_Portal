<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainer_id', 'date', 'session_time', 'day_name',
        'status', 'started_at', 'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    // ── Scopes ──────────────────────────────────
    public function scopeForTrainer($query, int $trainerId)
    {
        return $query->where('trainer_id', $trainerId);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->whereDate('date', $date);
    }

    // ── Relations ───────────────────────────────
    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // ── Helpers ─────────────────────────────────
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
