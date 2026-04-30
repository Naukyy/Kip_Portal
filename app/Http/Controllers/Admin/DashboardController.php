<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Salary;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get overall statistics for admin dashboard
        
        // Total active users by role
        $totalTrainers = User::whereIn('role', ['Trainer Senior', 'Trainer Junior'])
            ->where('is_active', true)
            ->count();
            
        $totalStudents = Student::where('is_active', true)->count();
        
        // Today's attendance count
        $todayAttendances = Attendance::whereDate('date', Carbon::today())->count();
        
// This month salary total
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $monthSalaries = Salary::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->sum('net_take_home');
            
        // Recent activities (last 10 attendances)
        $recentAttendances = Attendance::orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Recent students (last 5 added)
        $recentStudents = Student::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Active trainers
        $trainers = User::whereIn('role', ['Trainer Senior', 'Trainer Junior'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('admin.dashboard', compact(
            'totalTrainers',
            'totalStudents',
            'todayAttendances',
            'monthSalaries',
            'recentAttendances',
            'recentStudents',
            'trainers'
        ));
    }
}
