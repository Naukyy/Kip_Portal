@extends('layouts.app')

@section('title', 'Dashboard Trainer')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Total Students --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Murid</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $totalStudents }}</p>
            </div>
        </div>
    </div>

    {{-- Today's Attendance --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Presensi Hari Ini</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $todayAttendances }}</p>
            </div>
        </div>
    </div>

    {{-- Month Attendance --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Bulan Ini</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $monthAttendances }}</p>
            </div>
        </div>
    </div>

    {{-- Active Status --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Status Aktif</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $totalStudents > 0 ? 'Aktif' : 'Kosong' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Recent Activity --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aktivitas Terbaru</h3>
    @if($recentAttendances->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3">Nama Murid</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentAttendances as $attendance)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3">{{ $attendance->student->name ?? 'Tanpa Nama' }}</td>
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                @if($attendance->status === 'Hadir')
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Hadir</span>
                                @elseif($attendance->status === 'Izin')
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700">Izin</span>
                                @elseif($attendance->status === 'Sakit')
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">Sakit</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">Alpha</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500 dark:text-gray-400 text-center py-4">Belum ada aktivitas terbaru.</p>
    @endif
</div>

{{-- Recent Students --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Murid Terbaru</h3>
    @if($recentStudents->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">No. HP</th>
                        <th class="px-4 py-3">Tanggal Daftar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentStudents as $student)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3">{{ $student->name }}</td>
                            <td class="px-4 py-3">{{ $student->phone ?? '-' }}</td>
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($student->created_at)->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500 dark:text-gray-400 text-center py-4">Belum ada murid.</p>
    @endif
</div>
@endsection
