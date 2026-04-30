<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $trainer = auth()->user();
        $date    = $request->date ? Carbon::parse($request->date) : Carbon::today();

        // Murid milik trainer ini
        $students = Student::where('trainer_id', $trainer->id)
            ->where('is_active', true)
            ->get();

        // Presensi yang sudah ada untuk tanggal ini
        $existingAttendances = Attendance::whereIn('student_id', $students->pluck('id'))
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        // Daftar trainer lain untuk dropdown substitute
        $otherTrainers = User::whereIn('role', ['Trainer Senior', 'Trainer Junior'])
            ->where('id', '!=', $trainer->id)
            ->where('is_active', true)
            ->get();

        return view('trainer.attendance.index', compact(
            'students', 'existingAttendances', 'date', 'otherTrainers'
        ));
    }

    public function store(StoreAttendanceRequest $request)
    {
        $trainer = auth()->user();
        $date    = Carbon::parse($request->date);

        foreach ($request->attendances as $studentId => $data) {
            Attendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $date->toDateString()],
                [
                    'trainer_id'             => $trainer->id,
                    'substitute_trainer_id'  => $data['substitute_id'] ?? null,
                    'status'                 => $data['status'],
                    'notes'                  => $data['notes'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Presensi berhasil disimpan!');
    }

    public function recap(Request $request)
    {
        $trainer = auth()->user();
        $month   = $request->month ?? now()->month;
        $year    = $request->year  ?? now()->year;

        $students = Student::where('trainer_id', $trainer->id)
            ->where('is_active', true)
            ->with(['attendances' => function ($q) use ($month, $year) {
                $q->whereMonth('date', $month)->whereYear('date', $year);
            }])
            ->get();

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        return view('trainer.attendance.recap', compact(
            'students', 'month', 'year', 'daysInMonth'
        ));
    }
}