<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $trainer = auth()->user();
        
        // Get stats for the trainer's dashboard
        $totalStudents = Student::where('trainer_id', $trainer->id)
            ->where('is_active', true)
            ->count();
            
        // Today's attendance
        $todayAttendances = Attendance::where('trainer_id', $trainer->id)
            ->whereDate('date', Carbon::today())
            ->count();
            
        // This month attendance stats
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        
        $monthAttendances = Attendance::where('trainer_id', $trainer->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->count();
            
        // Recent attendances (last 5)
        $recentAttendances = Attendance::where('trainer_id', $trainer->id)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Upcoming students (recently added)
        $recentStudents = Student::where('trainer_id', $trainer->id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return view('trainer.dashboard', compact(
            'totalStudents',
            'todayAttendances',
            'monthAttendances',
            'recentAttendances',
            'recentStudents'
        ));
    }
}
