<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salary extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'year',
        'base_earnings',
        'total_incentives',
        'total_deductions',
        'net_take_home',
        'status',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'base_earnings' => 'decimal:2',
            'total_incentives' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_take_home' => 'decimal:2',
            'finalized_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForCurrentMonth($query)
    {
        return $query->where('month', now()->month)
                    ->where('year', now()->year);
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', 'Finalized');
    }
}
