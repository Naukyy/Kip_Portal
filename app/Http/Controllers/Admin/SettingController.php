<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalaryTier;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $tiers = SalaryTier::orderBy('min_attendance')->get();
        $categories = TransactionCategory::orderBy('name')->get();
        
        return view('admin.settings.index', compact('tiers', 'categories'));
    }
    
    public function updateTiers(Request $request)
    {
        $request->validate([
            'tiers' => ['required', 'array'],
            'tiers.*.id' => ['nullable', 'exists:salary_tiers,id'],
            'tiers.*.name' => ['required', 'string', 'max:255'],
            'tiers.*.min_attendance' => ['required', 'integer', 'min:0'],
            'tiers.*.base_salary' => ['required', 'numeric', 'min:0'],
        ]);
        
        foreach ($request->tiers as $tierData) {
            if (!empty($tierData['id'])) {
                $tier = SalaryTier::find($tierData['id']);
                if ($tier) {
                    $tier->update([
                        'name' => $tierData['name'],
                        'min_attendance' => $tierData['min_attendance'],
                        'base_salary' => $tierData['base_salary'],
                    ]);
                }
            } else {
                SalaryTier::create([
                    'name' => $tierData['name'],
                    'min_attendance' => $tierData['min_attendance'],
                    'base_salary' => $tierData['base_salary'],
                ]);
            }
        }
        
        return back()->with('success', 'Tier salary berhasil diperbarui!');
    }
    
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:transaction_categories,name'],
            'type' => ['required', 'in:allowance,deduction'],
            'description' => ['nullable', 'string'],
        ]);
        
        TransactionCategory::create($validated);
        
        return back()->with('success', 'Kategori berhasil ditambahkan!');
    }
    
    public function deleteCategory(TransactionCategory $category)
    {
        $category->delete();
        
        return back()->with('success', 'Kategori berhasil dihapus!');
    }
}
