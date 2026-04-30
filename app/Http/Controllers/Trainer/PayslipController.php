<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Services\PayrollCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    public function __construct(private PayrollCalculator $calculator) {}

    public function index(Request $request)
    {
        $trainer = auth()->user();
        $month   = $request->month ?? now()->month;
        $year    = $request->year  ?? now()->year;

        $payslipData = $this->calculator->calculateNet($trainer, (int)$month, (int)$year);

        return view('trainer.payslip.index', compact('payslipData', 'month', 'year', 'trainer'));
    }

    public function exportPdf(int $month, int $year)
    {
        $trainer     = auth()->user();
        $payslipData = $this->calculator->calculateNet($trainer, $month, $year);

        $pdf = Pdf::loadView('trainer.payslip.pdf', compact('payslipData', 'month', 'year', 'trainer'))
            ->setPaper('A4', 'portrait');

        $filename = "payslip-{$trainer->employee_code}-{$year}-{$month}.pdf";
        return $pdf->download($filename);
    }
}