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
            'student_id' => ['nullable', 'string', 'max:20', 'unique:students'],
            'name' => ['required', 'string', 'max:255'],
            'trainer_id' => ['required', 'exists:users,id'],
            'periode' => ['nullable', 'string', 'max:100'],
            'session_time' => ['nullable', 'string', 'max:50'],
            'schedule' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);
        
        // Auto-generate student_id if not provided
        if (empty($validated['student_id'])) {
            $lastStudent = Student::orderBy('id', 'desc')->first();
            $nextNumber = $lastStudent ? (intval(substr($lastStudent->student_id, 4)) + 1) : 1;
            $validated['student_id'] = 'STD-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }
        
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
            'student_id' => ['nullable', 'string', 'max:20', 'unique:students,student_id,' . $student->id],
            'name' => ['required', 'string', 'max:255'],
            'trainer_id' => ['required', 'exists:users,id'],
            'periode' => ['nullable', 'string', 'max:100'],
            'session_time' => ['nullable', 'string', 'max:50'],
            'schedule' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
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
    
    /**
     * AJAX: Inline update for inline editing
     */
    public function inlineUpdate(Request $request, Student $student)
    {
        $field = $request->input('field');
        $value = $request->input('value');
        
// Validate allowed fields
        $allowedFields = ['student_id', 'name', 'periode', 'schedule', 'session_time', 'phone', 'email', 'trainer_id', 'parent_name', 'parent_phone', 'address', 'is_active'];
        
        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Invalid field'], 422);
        }
        
        // Special validation for certain fields
        if ($field === 'email' && $value) {
            $request->validate(['value' => 'email']);
        }
        if ($field === 'trainer_id' && $value) {
            $request->validate(['value' => 'exists:users,id']);
        }
        
        try {
            $student->update([$field => $value ?: null]);
            return response()->json(['success' => true, 'message' => 'Updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * AJAX: Inline delete
     */
    public function inlineDelete(Student $student)
    {
        try {
            $studentName = $student->name;
            $student->delete();
            return response()->json(['success' => true, 'message' => "Student $studentName deleted"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Get trainers list for dropdown
     */
    public function getTrainers(Request $request)
    {
        $trainers = User::whereIn('role', ['Trainer Senior', 'Trainer Junior'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
            
        return response()->json($trainers);
    }
}
