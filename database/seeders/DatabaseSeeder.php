<?php

namespace Database\Seeders;

use App\Models\SalaryTier;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'employee_code' => 'ADM001',
            'name'          => 'Admin KIP',
            'nickname'      => 'Admin',
            'email'         => 'admin@kip.id',
            'password'      => Hash::make('123456'),
            'role'          => 'Admin',
        ]);

        // Trainer Senior
        User::create([
            'employee_code' => 'TRS001',
            'name'          => 'Budi Santoso',
            'nickname'      => 'Budi',
            'email'         => 'budi@kip.id',
            'password'      => Hash::make('password'),
            'role'          => 'Trainer Senior',
        ]);

        // Trainer Junior
        User::create([
            'employee_code' => 'TRJ001',
            'name'          => 'Siti Rahayu',
            'nickname'      => 'Siti',
            'email'         => 'siti@kip.id',
            'password'      => Hash::make('password'),
            'role'          => 'Trainer Junior',
        ]);

        // Salary Tiers
        $tiers = [
            ['label' => 'Tier 1 (1–3 murid)',  'min' => 1, 'max' => 3,  'senior' => 30000, 'junior' => 20000, 'order' => 1],
            ['label' => 'Tier 2 (4–5 murid)',  'min' => 4, 'max' => 5,  'senior' => 40000, 'junior' => 27000, 'order' => 2],
            ['label' => 'Tier 3 (6–7 murid)',  'min' => 6, 'max' => 7,  'senior' => 55000, 'junior' => 35000, 'order' => 3],
            ['label' => 'Tier 4 (8+ murid)',   'min' => 8, 'max' => 99, 'senior' => 70000, 'junior' => 45000, 'order' => 4],
        ];

        foreach ($tiers as $t) {
            SalaryTier::create([
                'label'        => $t['label'],
                'min_students' => $t['min'],
                'max_students' => $t['max'],
                'rate_senior'  => $t['senior'],
                'rate_junior'  => $t['junior'],
                'sort_order'   => $t['order'],
            ]);
        }

        // Transaction Categories
        $categories = [
            ['type' => 'Incentive', 'name' => 'Bonus Kinerja'],
            ['type' => 'Incentive', 'name' => 'Bonus Kehadiran Penuh'],
            ['type' => 'Incentive', 'name' => 'Lembur'],
            ['type' => 'Deduction', 'name' => 'Potongan Keterlambatan'],
            ['type' => 'Deduction', 'name' => 'Potongan Absen Tanpa Keterangan'],
            ['type' => 'Deduction', 'name' => 'Potongan Pinjaman'],
        ];

        foreach ($categories as $c) {
            \App\Models\TransactionCategory::create($c);
        }
    }
}