<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Salary;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon; // Library untuk urusan waktu
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Menghitung jumlah Trainer aktif (Senior & Junior)
        $totalTrainers = User::whereIn('role', ['Trainer Senior', 'Trainer Junior'])
            ->where('is_active', true)
            ->count();
            
        // Menghitung jumlah Murid yang masih aktif les
        $totalStudents = Student::where('is_active', true)->count();
        
        // Menghitung berapa banyak absensi yang masuk KHUSUS hari ini
        $todayAttendances = Attendance::whereDate('date', Carbon::today())->count();
        
        // Mengambil bulan dan tahun saat ini untuk laporan gaji
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Menghitung total pengeluaran gaji (Take Home Pay) bulan ini
        $monthSalaries = Salary::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->sum('net_take_home');
            
        // Mengambil 10 data absensi terbaru untuk ditampilkan di tabel "Aktivitas Terbaru"
        $recentAttendances = Attendance::orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        // Mengambil 5 murid yang paling baru didaftarkan
        $recentStudents = Student::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Mengambil daftar trainer aktif untuk keperluan dropdown atau list
        $trainers = User::whereIn('role', ['Trainer Senior', 'Trainer Junior'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        // Mengirimkan semua angka-angka tadi ke tampilan Dashboard Admin
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