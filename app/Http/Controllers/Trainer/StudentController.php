<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $trainer = auth()->user();
        
        $students = Student::where('trainer_id', $trainer->id)
            ->where('is_active', true)
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                      ->orWhere('phone', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10);
            
return view('trainer.students.list', compact('students'));
    }
    
    public function show(Student $student)
    {
        $trainer = auth()->user();
        
        // Ensure the trainer can only view their own students
        if ($student->trainer_id !== $trainer->id) {
            abort(403, 'Akses tidak diizinkan.');
        }
        
        $student->load(['attendances' => function ($query) {
            $query->orderBy('date', 'desc')->limit(30);
        }]);
        
return view('trainer.students.detail', compact('student'));
    }
}
