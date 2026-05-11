<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassMeeting;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Sumber 1: siswa aktif yang memang memiliki jadwal hari ini
        $students = Student::where('trainer_id', $trainer->id)
            ->where('is_active', true)
            ->where('schedule', 'like', "%{$dayName}%")
            ->orderBy('session_time')
            ->get();

        // Kelompokkan berdasarkan session_time (jadwal hari ini)
        $sessionGroups = $students->groupBy('session_time');

        // Sumber 2: siswa yang dipindahkan sebelumnya (attendance pada tanggal ini)
        // agar UI tetap menampilkan sesi target dan jumlah muridnya, walaupun jadwal asli hari itu kosong.
        $movedAttendances = Attendance::query()
            ->where('attendances.trainer_id', $trainer->id)
            ->where('attendances.date', $date->toDateString())
            ->whereIn('attendances.status', ['hadir', 'alpha', 'sakit', 'izin', 'pending'])
            ->join('class_meetings', 'class_meetings.id', '=', 'attendances.class_meeting_id')
            ->select('attendances.student_id', 'class_meetings.session_time')
            ->distinct()
            ->get();

        if ($movedAttendances->isNotEmpty()) {
            $movedStudentIds = $movedAttendances->pluck('student_id')->unique()->values();
            $movedStudents = Student::where('trainer_id', $trainer->id)
                ->where('is_active', true)
                ->whereIn('id', $movedStudentIds)
                ->orderBy('name')
                ->get();

            // Buat map student_id => student
            $studentMap = $movedStudents->keyBy('id');

            // Kelompokkan berdasarkan session_time hasil attendance target
            $movedGroups = $movedAttendances->groupBy('session_time')->map(function ($rows) use ($studentMap) {
                return $rows
                    ->pluck('student_id')
                    ->unique()
                    ->map(fn ($id) => $studentMap->get($id))
                    ->filter();
            });

            // Merge ke sessionGroups (gabungkan koleksi)
            foreach ($movedGroups as $sessionTime => $movedGroup) {
                if (!isset($sessionGroups[$sessionTime])) {
                    $sessionGroups[$sessionTime] = collect();
                }

                $sessionGroups[$sessionTime] = $sessionGroups[$sessionTime]
                    ->concat($movedGroup)
                    ->unique('id')
                    ->values();
            }
        }


        // Ambil session_time yang sudah memiliki Attendance pada tanggal ini
        // (agar kelas hasil move tetap muncul meskipun schedule murid hari target tidak ada)
        $attendanceSessionTimes = Attendance::query()
            ->join('class_meetings', 'class_meetings.id', '=', 'attendances.class_meeting_id')
            ->where('attendances.trainer_id', $trainer->id)
            ->where('attendances.date', $date->toDateString())
            ->distinct()
            ->pluck('class_meetings.session_time')
            ->values();


        $allSessionTimes = $sessionGroups->keys()
            ->concat($attendanceSessionTimes)
            ->unique()
            ->values();

        // Kalau schedule kosong tapi ada data attendance (mis. hasil pindahan),
        // sessionGroups juga harus tetap punya key supaya view tidak menganggap hari ini kosong.
        if ($sessionGroups->isEmpty() && $allSessionTimes->isNotEmpty()) {
            $sessionGroups = $allSessionTimes->mapWithKeys(fn ($t) => [$t => collect()]);
        }

        // Ambil / buat class meeting untuk setiap sesi di tanggal ini
        $meetings = [];
        foreach ($allSessionTimes as $sessionTime) {
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
        $scheduledStudents = Student::where('trainer_id', $trainer->id)
            ->where('is_active', true)
            ->where('session_time', $meeting->session_time)
            ->where('schedule', 'like', "%{$dayName}%")
            ->orderBy('name')
            ->get();

        // Ambil siswa yang sudah punya Attendance pada meeting ini (walau tidak sesuai schedule)
        $attendanceStudentIds = Attendance::where('class_meeting_id', $meeting->id)
            ->where('trainer_id', $trainer->id)
            ->pluck('student_id')
            ->values();

        $movedStudents = Student::where('trainer_id', $trainer->id)
            ->where('is_active', true)
            ->whereIn('id', $attendanceStudentIds)
            ->orderBy('name')
            ->get();

        // Union + unik berdasarkan student id
        $students = $scheduledStudents
            ->concat($movedStudents)
            ->unique('id')
            ->values();

        // Inisialisasi attendance record (pending) untuk siswa yang akan ditampilkan
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

        return view('trainer.attendance.attendance_fixed', compact(
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

        // normal update
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

    // AJAX: Move attendance when status is izin/sakit
    // - create/update Attendance on target_date + target_session_time
    // - if Attendance on target exists with status != pending => reject (422)
    // - status on current meeting will be handled by frontend (set to izin/sakit)
    public function moveStatus(Request $request, ClassMeeting $meeting)
    {
        $trainer = auth()->user();
        // catatan: VSCode Intelephense bisa memberi warning untuk auth()->user(), namun saat runtime biasanya normal.
        // Fokus fix bug ada di rollback/cleanup pemindahan.

        abort_if($meeting->trainer_id !== $trainer->id, 403);
        abort_if($meeting->status !== 'in_progress', 422, 'Kelas belum dimulai.');

        $request->validate([
            'student_id'          => 'required|exists:students,id',
            'status'              => 'required|in:sakit,izin',
            'target_date'         => 'required|date',
            'target_session_time' => 'required|string',
        ]);

        $targetDate = Carbon::parse($request->target_date)->toDateString();
        $targetSessionTime = $request->target_session_time;
        $status = $request->status;


        // Pastikan student milik trainer
        abort_if(!Student::where('id', $request->student_id)->where('trainer_id', $trainer->id)->exists(), 403);

        return DB::transaction(function () use ($request, $meeting, $trainer, $targetDate, $targetSessionTime, $status) {
            // Cari apakah student sudah punya attendance pada tanggal target untuk sesi yang dituju
            // (gunakan join ke class_meetings agar session_time benar + pastikan milik trainer yang sama)
            $existing = Attendance::query()
                ->where('attendances.trainer_id', $trainer->id)
                ->where('attendances.student_id', $request->student_id)
                ->where('attendances.date', $targetDate)
                ->join('class_meetings', 'class_meetings.id', '=', 'attendances.class_meeting_id')
                ->where('class_meetings.trainer_id', $trainer->id)
                ->where('class_meetings.session_time', $targetSessionTime)
                ->select('attendances.*')
                ->first();

            $dayName = Carbon::parse($targetDate)->locale('id')->dayName;

            // FIX: blok pemindahan jika siswa memang terdaftar pada dayName target.
            // Ini menutup kasus "try pertama" ketika existing masih pending (karena record baru dibuat),
            // tapi secara jadwal siswa sudah benar-benar ada di hari tersebut.
            $isScheduledOnTarget = Student::where('id', $request->student_id)
                ->where('trainer_id', $trainer->id)
                ->where('is_active', true)
                ->where('schedule', 'like', "%{$dayName}%")
                ->exists();

            if ($isScheduledOnTarget) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa sudah terdaftar di hari tersebut. Kehadiran tidak berpindah.',
                ], 422);
            }

            // Tolak pemindahan jika pada tanggal/sesi target murid sudah punya attendance (selain pending).
            // Gunakan message yang spesifik agar UI menampilkan notifikasi yang benar.
            if ($existing && $existing->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa sudah terdaftar di hari tersebut. Kehadiran tidak berpindah.',
                ], 422);
            }

            // Pastikan student memang punya jadwal di dayName target.
            // Jika tidak terdaftar, tetap boleh dibuat attendance untuk kebutuhan pindahan.
            // Namun kasus salah hari umumnya justru: murid sudah terdaftar => existing harusnya != pending
            // sehingga akan ditolak di bagian pengecekan di atas.

            // Ensure class meeting exists for target date + target session time
            $targetMeeting = ClassMeeting::firstOrCreate(
                [
                    'trainer_id' => $trainer->id,
                    'date' => $targetDate,
                    'session_time' => $targetSessionTime,
                ],
                [
                    'day_name' => $dayName,
                    'status' => 'pending',
                ]
            );

            // Create/update attendance on target meeting
            // Jika record pending sudah ada, update menjadi izin/sakit (sesuai tujuan pemindahan)
            Attendance::updateOrCreate(
                [
                    'class_meeting_id' => $targetMeeting->id,
                    'student_id' => $request->student_id,
                    'date' => $targetDate,
                ],
                [
                    'trainer_id' => $trainer->id,
                    'status' => $status,
                ]
            );

            // Update status di meeting asal (siswa ditandai izin/sakit)
            Attendance::where('class_meeting_id', $meeting->id)
                ->where('student_id', $request->student_id)
                ->update([
                    'status' => $status,
                    'trainer_id' => $trainer->id,
                ]);

            return response()->json([
                'success' => true,
                'target_meeting_id' => $targetMeeting->id,
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────
    //  AJAX: End Class — ubah meeting status → completed
    // ─────────────────────────────────────────────────────────────
    public function endClass(Request $request, ClassMeeting $meeting)
    {
        abort_if($meeting->trainer_id !== auth()->id(), 403);
        abort_if($meeting->status !== 'in_progress', 422, 'Kelas belum dimulai.');

        // Pastikan tidak ada siswa yang masih pending pada meeting ini.
        // Catatan: untuk izin/sakit yang dipindahkan, siswa menjadi 'izin'/'sakit'
        // sehingga tidak akan menghambat end class.
        $pendingCount = Attendance::where('class_meeting_id', $meeting->id)
            ->where('status', 'pending')
            ->count();


        if ($pendingCount > 0) {
            // FIX: rollback pemindahan izin/sakit yang terlanjur dibuat ke meeting lain saat meeting asal gagal di-end.
            // Karena pada flow ini, moveStatus() bisa sudah membuat Attendance target (status izin/sakit),
            // sehingga tanggal target jadi 'terkunci' dan tidak bisa dipindah lagi.
            //
            // Cara rollback yang paling aman tanpa skema tambahan: kembalikan siswa yang dipindahkan pada meeting asal
            // sekaligus hapus record target untuk siswa yang sama yang berada pada tanggal yang sama dengan meeting asal.

            // 1) Ambil daftar siswa di meeting asal yang statusnya sudah izin/sakit (yang berpotensi dipindah)
            $movedStudentIds = Attendance::where('class_meeting_id', $meeting->id)
                ->whereIn('status', ['izin', 'sakit'])
                ->pluck('student_id')
                ->values();

            if ($movedStudentIds->isNotEmpty()) {
                // 2) Hapus attendance target untuk siswa tersebut yang status izin/sakit dan berada pada tanggal meeting asal,
                //    supaya moveStatus sebelumnya tidak meninggalkan 'tanggal terkunci' untuk sesi yang sama.
                //    (Jika user memindah ke tanggal berbeda, recordnya juga bisa dianggap draft, namun tanpa penanda
                //    kita tidak bisa membedakan mana yang dibuat oleh batch moveStatus ini.)
                Attendance::where('trainer_id', $meeting->trainer_id)
                    ->whereIn('student_id', $movedStudentIds)
                    ->where('date', $meeting->date->toDateString())
                    ->whereIn('status', ['izin', 'sakit'])
                    // hanya hapus yang berada di kelas lain pada date+student yang sama,
                    // karena itu sisa pemindahan yang dilakukan saat meeting asal gagal.
                    ->where('class_meeting_id', '!=', $meeting->id)
                    ->delete();


                // 3) Kembalikan status pada meeting asal menjadi pending (biar endClass tetap ditolak sesuai pendingCount asli)
                Attendance::where('class_meeting_id', $meeting->id)
                    ->whereIn('student_id', $movedStudentIds)
                    ->update([
                        'status' => 'pending',
                    ]);
            }

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
        $month   = (int) ($request->month ?? now()->month);
        $year    = (int) ($request->year  ?? now()->year);
        $sessionTime = $request->session_time ?: null;

        // daftar sesi (session_time) untuk trainer ini
        $sessions = Student::where('trainer_id', $trainer->id)
            ->where('is_active', true)
            ->distinct()
            ->orderBy('session_time')
            ->pluck('session_time')
            ->filter()
            ->values();

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $students = Student::where('trainer_id', $trainer->id)
            ->where('is_active', true)
            // ketika sesi dipilih, hanya ambil murid yang berada di sesi tsb
            ->when($sessionTime, fn ($q) => $q->where('session_time', $sessionTime))
            ->with(['attendances' => function ($q) use ($month, $year, $sessionTime) {
                $q->whereMonth('date', $month)->whereYear('date', $year);

                // filter berdasarkan sesi pada level attendance via relasi classMeeting
                if ($sessionTime) {
                    $q->whereHas('classMeeting', function ($cq) use ($sessionTime) {
                        $cq->where('session_time', $sessionTime);
                    });
                }
            }])
            ->get();

        return view('trainer.attendance.recap', compact(
            'students', 'month', 'year', 'daysInMonth', 'sessions', 'sessionTime',
        ))->with([
            'selectedSessionTime' => $sessionTime,
            'currentYear' => now()->year,
        ]);
    }
}