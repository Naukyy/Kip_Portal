<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryTier extends Model
{
    protected $fillable = [
        'label', 'min_students', 'max_students',
        'rate_senior', 'rate_junior', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rate_senior' => 'decimal:2',
            'rate_junior' => 'decimal:2',
            'is_active'   => 'boolean',
        ];
    }

    // Cari tier yang cocok berdasarkan jumlah murid hadir
    public static function findTier(int $studentCount): ?self
    {
        return self::where('is_active', true)
            ->where('min_students', '<=', $studentCount)
            ->where('max_students', '>=', $studentCount)
            ->orderBy('sort_order')
            ->first();
    }
}