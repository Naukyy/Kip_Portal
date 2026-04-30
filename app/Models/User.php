<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

protected $fillable = [
        'employee_code', 'name', 'nickname', 'whatsapp', 'email', 'password',
        'phone', 'role', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Helpers ─────────────────────────────────
    public function isAdmin(): bool
    {
        return in_array($this->role, ['Admin', 'Management']);
    }

    public function isTrainer(): bool
    {
        return in_array($this->role, ['Trainer Senior', 'Trainer Junior']);
    }

    public function isSenior(): bool
    {
        return $this->role === 'Trainer Senior';
    }

    // ── Relasi ─────────────────────────────────
    public function students()
    {
        return $this->hasMany(Student::class, 'trainer_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'trainer_id');
    }

    public function substituteAttendances()
    {
        return $this->hasMany(Attendance::class, 'substitute_trainer_id');
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function salaryTransactions()
    {
        return $this->hasMany(SalaryTransaction::class);
    }
}