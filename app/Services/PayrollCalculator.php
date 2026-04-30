<?php
// app/Services/PayrollCalculator.php

namespace App\Services;

use App\Models\Attendance;
use App\Models\SalaryTier;
use App\Models\SalaryTransaction;
use App\Models\User;
use Carbon\Carbon;

class PayrollCalculator
{
    /**
     * Hitung base earnings seorang trainer untuk bulan tertentu.
     * Logic: tiap hari, hitung murid yang Hadir + murid yang di-cover trainer ini
     * → cocokkan dengan tier → ambil rate sesuai level trainer
     */
    public function calculateBaseEarnings(User $trainer, int $month, int $year): array
    {
        $dailyBreakdown = [];
        $totalBase = 0;

        // Semua hari dalam bulan
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);

            // Murid hadir yang diasuh trainer ini (bukan substitute)
            $ownAttend = Attendance::where('trainer_id', $trainer->id)
                ->whereNull('substitute_trainer_id') // dia sendiri yang ngajar
                ->whereDate('date', $date)
                ->where('status', 'Attend')
                ->count();

            // Murid yang di-cover oleh trainer ini (substitute)
            $subAttend = Attendance::where('substitute_trainer_id', $trainer->id)
                ->whereDate('date', $date)
                ->where('status', 'Attend')
                ->count();

            $totalStudents = $ownAttend + $subAttend;

            if ($totalStudents === 0) {
                continue; // tidak ada kelas, tidak ada gaji hari ini
            }

            $tier = SalaryTier::findTier($totalStudents);

            if (!$tier) {
                continue; // tidak ada tier yang cocok
            }

            $rate = $trainer->isSenior() ? $tier->rate_senior : $tier->rate_junior;
            $dailyEarning = (float) $rate;

            $dailyBreakdown[] = [
                'date'          => $date->toDateString(),
                'own_students'  => $ownAttend,
                'sub_students'  => $subAttend,
                'total'         => $totalStudents,
                'tier'          => $tier->label,
                'rate'          => $dailyEarning,
            ];

            $totalBase += $dailyEarning;
        }

        return [
            'breakdown' => $dailyBreakdown,
            'total'     => $totalBase,
        ];
    }

    /**
     * Hitung total gaji bersih (net take-home) trainer.
     */
    public function calculateNet(User $trainer, int $month, int $year): array
    {
        $base = $this->calculateBaseEarnings($trainer, $month, $year);

        // Ambil transaksi (incentive/deduction) di bulan tersebut
        $transactions = SalaryTransaction::where('user_id', $trainer->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $incentives  = $transactions->where('type', 'Incentive')->sum('amount');
        $deductions  = $transactions->where('type', 'Deduction')->sum('amount');
        $net         = $base['total'] + $incentives - $deductions;

        return [
            'base_earnings'    => $base['total'],
            'daily_breakdown'  => $base['breakdown'],
            'transactions'     => $transactions,
            'total_incentives' => $incentives,
            'total_deductions' => $deductions,
            'net_take_home'    => max(0, $net), // tidak boleh minus
        ];
    }

    /**
     * Hitung & simpan gaji semua trainer ke tabel salaries.
     */
    public function recalculateAll(int $month, int $year): void
    {
        $trainers = User::whereIn('role', ['Trainer Senior', 'Trainer Junior'])
            ->where('is_active', true)
            ->get();

        foreach ($trainers as $trainer) {
            $result = $this->calculateNet($trainer, $month, $year);

            \App\Models\Salary::updateOrCreate(
                ['user_id' => $trainer->id, 'month' => $month, 'year' => $year],
                [
                    'base_earnings'    => $result['base_earnings'],
                    'total_incentives' => $result['total_incentives'],
                    'total_deductions' => $result['total_deductions'],
                    'net_take_home'    => $result['net_take_home'],
                    'status'           => 'Draft',
                ]
            );
        }
    }
}