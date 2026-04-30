<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecapController extends Controller
{
    public function index(Request $request)
    {
        $month    = $request->month    ?? now()->month;
        $year     = $request->year     ?? now()->year;
        $trainerId = $request->trainer_id;
        $session  = $request->session;

        $query = Student::with(['trainer', 'attendances' => function ($q) use ($month, $year) {
            $q->whereMonth('date', $month)->whereYear('date', $year);
        }])->where('is_active', true);

        if ($trainerId) {
            $query->where('trainer_id', $trainerId);
        }
        if ($session) {
            $query->where('session_time', $session);
        }

        $students    = $query->get();
        $trainers    = User::whereIn('role', ['Trainer Senior', 'Trainer Junior'])->get();
        $sessions    = Student::distinct()->pluck('session_time')->filter()->sort()->values();
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        return view('admin.recap.index', compact(
            'students', 'trainers', 'sessions', 'month', 'year', 'daysInMonth', 'trainerId', 'session'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $month = $request->month ?? now()->month;
        $year  = $request->year  ?? now()->year;
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $students = Student::with(['trainer', 'attendances' => function ($q) use ($month, $year) {
            $q->whereMonth('date', $month)->whereYear('date', $year);
        }])->where('is_active', true)->get();

        $filename = "rekap-presensi-{$year}-{$month}.csv";

        return response()->streamDownload(function () use ($students, $daysInMonth, $month, $year) {
            $handle = fopen('php://output', 'w');

            // Header: ID, Nama, Trainer, + hari 1..31
            $header = ['ID Siswa', 'Nama', 'Trainer'];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $header[] = $d;
            }
            $header[] = 'Total Hadir';
            fputcsv($handle, $header);

            foreach ($students as $student) {
                $att = $student->attendances->keyBy(fn($a) => $a->date->day);
                $row = [
                    $student->student_id,
                    $student->name,
                    $student->trainer?->name ?? '-',
                ];
                $attendCount = 0;
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $status = $att[$d]->status ?? '-';
                    $row[] = $status === 'Attend' ? 'H' : ($status === 'Permission' ? 'I' : ($status === 'Absent' ? 'A' : '-'));
                    if ($status === 'Attend') $attendCount++;
                }
                $row[] = $attendCount;
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}