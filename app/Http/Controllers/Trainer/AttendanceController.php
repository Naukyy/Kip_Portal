<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassMeeting;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  INDEX — Halaman jadwal harian dengan date picker
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $trainer = auth()->user();
        $date    = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $dayName = $date->locale('id')->dayName; // e.g. "Senin"

        // Ambil siswa aktif trainer ini yang memiliki jadwal di hari ini
        $students = Student::where('trainer_id', $trainer->id)
            ->where('is_active', true)
            ->where('schedule', 'like', "%{$dayName}%")
            ->orderBy('session_time')
            ->get();

        // Kelompokkan berdasarkan session_time
        $sessionGroups = $students->groupBy('session_time');

        // Ambil / buat class meeting untuk setiap sesi di tanggal ini
        $meetings = [];
        foreach ($sessionGroups as $sessionTime => $sessionStudents) {
            $meeting = ClassMeeting::firstOrCreate(
                [
                    'trainer_id'   => $trainer->id,
                    'date'         => $date->toDateString(),
                    'session_time' => $sessionTime,
                ],
                [
                    'day_name' => $dayName,
                    'status'   => 'pending',
                ]
            );
            $meetings[$sessionTime] = $meeting;
        }

        return view('trainer.attendance.index', compact(
            'date', 'dayName', 'sessionGroups', 'meetings'
        ));
    }

    // ─────────────────────────────────────────────────────────────
    //  SHOW — Halaman presensi SPA untuk satu sesi
    // ─────────────────────────────────────────────────────────────
    public function show(Request $request, ClassMeeting $meeting)
    {
        $trainer = auth()->user();

        // Pastikan meeting ini milik trainer yang login
        abort_if($meeting->trainer_id !== $trainer->id, 403);

        // Ambil siswa untuk sesi ini (session_time cocok) dan sesuai jadwal hari ini
        $dayName = $meeting->date->locale('id')->dayName;
        $students = Student::where('trainer_id', $trainer->id)
            ->where('is_active', true)
            ->where('session_time', $meeting->session_time)
            ->where('schedule', 'like', "%{$dayName}%")
            ->orderBy('name')
            ->get();

        // Inisialisasi attendance record (pending) untuk siswa yang belum ada
        foreach ($students as $student) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'date'       => $meeting->date->toDateString(),
                ],
                [
                    'class_meeting_id' => $meeting->id,
                    'trainer_id'       => $trainer->id,
                    // Status default 'pending' akan digunakan jika record baru dibuat
                ]
            );
        }

        // Reload attendances
        $attendances = Attendance::where('class_meeting_id', $meeting->id)
            ->with('student')
            ->get()
            ->keyBy('student_id');

        $date = $meeting->date;

        // Prepare student data for Alpine.js
        $studentData = $students->map(fn($s) => [
            'id'         => $s->id,
            'name'       => $s->name,
            'student_id' => $s->student_id,
            'initials'   => strtoupper(substr($s->name, 0, 2)),
            'status'     => $attendances->get($s->id)?->status ?? 'pending',
        ]);

        return view('trainer.attendance.attendance', compact(
            'meeting', 'students', 'attendances', 'date', 'studentData'
        ));
    }

    // ─────────────────────────────────────────────────────────────
    //  AJAX: Start Class — ubah meeting status → in_progress
    // ─────────────────────────────────────────────────────────────
    public function startClass(Request $request, ClassMeeting $meeting)
    {
        abort_if($meeting->trainer_id !== auth()->id(), 403);
        abort_if($meeting->status !== 'pending', 422);

        $meeting->update([
            'status'     => 'in_progress',
            'started_at' => now()->format('H:i:s'),
        ]);

        return response()->json(['success' => true, 'started_at' => $meeting->started_at]);
    }

    // ─────────────────────────────────────────────────────────────
    //  AJAX: Update Attendance Status — set status siswa
    // ─────────────────────────────────────────────────────────────
    public function updateStatus(Request $request, ClassMeeting $meeting)
    {
        abort_if($meeting->trainer_id !== auth()->id(), 403);
        abort_if($meeting->status !== 'in_progress', 422, 'Kelas belum dimulai.');

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'status'     => 'required|in:pending,hadir,alpha,sakit,izin',
        ]);

        $attendance = Attendance::where('class_meeting_id', $meeting->id)
            ->where('student_id', $request->student_id)
            ->firstOrFail();

        $attendance->update(['status' => $request->status]);

        return response()->json([
            'success'    => true,
            'student_id' => $attendance->student_id,
            'status'     => $attendance->status,
            'label'      => $attendance->statusLabel(),
            'color'      => $attendance->statusColor(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  AJAX: End Class — ubah meeting status → completed
    // ─────────────────────────────────────────────────────────────
    public function endClass(Request $request, ClassMeeting $meeting)
    {
        abort_if($meeting->trainer_id !== auth()->id(), 403);
        abort_if($meeting->status !== 'in_progress', 422, 'Kelas belum dimulai.');

        // Pastikan tidak ada siswa yang masih pending
        $pendingCount = Attendance::where('class_meeting_id', $meeting->id)
            ->where('status', 'pending')
            ->count();

        if ($pendingCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Masih ada {$pendingCount} siswa yang belum diabsen.",
            ], 422);
        }

        $meeting->update([
            'status'   => 'completed',
            'ended_at' => now()->format('H:i:s'),
        ]);

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────────────────────
    //  LEGACY store (tetap ada agar route tidak broken)
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        return back()->with('info', 'Gunakan halaman presensi baru.');
    }

    // ─────────────────────────────────────────────────────────────
    //  RECAP
    // ─────────────────────────────────────────────────────────────
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