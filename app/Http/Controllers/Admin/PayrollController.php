<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salary;
use App\Models\User;
use App\Services\PayrollCalculator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollController extends Controller
{
    public function __construct(private PayrollCalculator $calculator) {}

    public function index(Request $request)
    {
        $month   = $request->month ?? now()->month;
        $year    = $request->year  ?? now()->year;

        $trainers = User::whereIn('role', ['Trainer Senior', 'Trainer Junior'])
            ->where('is_active', true)
            ->with(['salaries' => fn($q) => $q->where('month', $month)->where('year', $year)])
            ->get()
            ->map(function ($trainer) use ($month, $year) {
                $salary = $trainer->salaries->first();
                return [
                    'trainer'    => $trainer,
                    'salary'     => $salary,
                    'calculated' => $salary ? null : null, // lazy — calculate on demand
                ];
            });

        return view('admin.payroll.index', compact('trainers', 'month', 'year'));
    }

    public function calculate(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;

        $this->calculator->recalculateAll((int)$month, (int)$year);

        return back()->with('success', "Payroll bulan {$month}/{$year} berhasil dihitung ulang.");
    }

    public function export(Request $request): StreamedResponse
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;

        $salaries = Salary::with('user')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $filename = "payroll-{$year}-{$month}.csv";

        return response()->streamDownload(function () use ($salaries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Kode', 'Nama', 'Role', 'Base Earnings',
                'Insentif', 'Deduksi', 'Net Take-Home', 'Status'
            ]);

            foreach ($salaries as $s) {
                fputcsv($handle, [
                    $s->user->employee_code,
                    $s->user->name,
                    $s->user->role,
                    $s->base_earnings,
                    $s->total_incentives,
                    $s->total_deductions,
                    $s->net_take_home,
                    $s->status,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}