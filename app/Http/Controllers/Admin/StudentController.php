<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = Student::with('trainer')
            ->when($request->trainer_id, function ($query) use ($request) {
                $query->where('trainer_id', $request->trainer_id);
            })
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                      ->orWhere('phone', 'like', "%{$request->search}%")
                      ->orWhere('parent_name', 'like', "%{$request->search}%");
                });
            })
            ->when($request->is_active !== null, function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            })
            ->orderBy('name')
            ->paginate(10);
            
        $trainers = User::whereIn('role', ['Trainer Senior', 'Trainer Junior'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('admin.students.index', compact('students', 'trainers'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trainer_id' => ['required', 'exists:users,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);
        
        Student::create($validated);
        
        return back()->with('success', 'Murid berhasil ditambahkan!');
    }
    
    public function show(Student $student)
    {
        $student->load(['trainer', 'attendances' => function ($query) {
            $query->orderBy('date', 'desc')->limit(30);
        }]);
        
        return view('admin.students.show', compact('student'));
    }
    
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'trainer_id' => ['required', 'exists:users,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);
        
        $student->update($validated);
        
        return back()->with('success', 'Murid berhasil diperbarui!');
    }
    
    public function destroy(Student $student)
    {
        $student->delete();
        
        return back()->with('success', 'Murid berhasil dihapus!');
    }
}
