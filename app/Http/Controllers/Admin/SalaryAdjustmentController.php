<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\Request;

class SalaryAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $salaries = Salary::with('trainer')
            ->when($request->trainer_id, function ($query) use ($request) {
                $query->where('trainer_id', $request->trainer_id);
            })
            ->when($request->month, function ($query) use ($request) {
                $query->whereMonth('payment_date', $request->month);
            })
            ->when($request->year, function ($query) use ($request) {
                $query->whereYear('payment_date', $request->year);
            })
            ->orderBy('payment_date', 'desc')
            ->paginate(10);
            
        $trainers = User::whereIn('role', ['Trainer Senior', 'Trainer Junior'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('admin.adjustments.index', compact('salaries', 'trainers'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'trainer_id' => ['required', 'exists:users,id'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020'],
            'type' => ['required', 'in:allowance,deduction'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
        
        // Find the salary record
        $salary = Salary::where('trainer_id', $validated['trainer_id'])
            ->whereMonth('payment_date', $validated['month'])
            ->whereYear('payment_date', $validated['year'])
            ->first();
        
        if (!$salary) {
            return back()->with('error', 'Gaji untuk periode ini tidak ditemukan!');
        }
        
        // Add adjustment
        $adjustments = $salary->adjustments ?? [];
        $adjustments[] = [
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'created_at' => now()->toDateTimeString(),
        ];
        
        $salary->update(['adjustments' => $adjustments]);
        
        // Recalculate net amount
        $grossAmount = $salary->gross_amount;
        $allowances = collect($adjustments)->where('type', 'allowance')->sum('amount');
        $deductions = collect($adjustments)->where('type', 'deduction')->sum('amount');
        
        $salary->update([
            'allowances' => $allowances,
            'deductions' => $deductions,
            'net_amount' => $grossAmount + $allowances - $deductions,
        ]);
        
        return back()->with('success', 'Penyesuaian berhasil ditambahkan!');
    }
    
    public function destroy(Request $request, Salary $salary, $adjustmentIndex)
    {
        $adjustments = $salary->adjustments ?? [];
        
        if (!isset($adjustments[$adjustmentIndex])) {
            return back()->with('error', 'Penyesuaian tidak ditemukan!');
        }
        
        unset($adjustments[$adjustmentIndex]);
        $adjustments = array_values($adjustments);
        
        $salary->update(['adjustments' => $adjustments]);
        
        // Recalculate
        $grossAmount = $salary->gross_amount;
        $allowances = collect($adjustments)->where('type', 'allowance')->sum('amount');
        $deductions = collect($adjustments)->where('type', 'deduction')->sum('amount');
        
        $salary->update([
            'allowances' => $allowances,
            'deductions' => $deductions,
            'net_amount' => $grossAmount + $allowances - $deductions,
        ]);
        
        return back()->with('success', 'Penyesuaian berhasil dihapus!');
    }
}
