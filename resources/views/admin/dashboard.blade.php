@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="p-6 md:p-8 max-w-7xl mx-auto w-full transition-colors duration-300">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total Trainers --}}
        <div class="bg-white dark:bg-[#111827] rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Trainer</p>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $totalTrainers }}</p>
                </div>
            </div>
        </div>

        {{-- Total Students --}}
        <div class="bg-white dark:bg-[#111827] rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div class="bg-white dark:bg-[#111827] rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Presensi Hari Ini</p>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $todayAttendances }}</p>
                </div>
            </div>
        </div>

        {{-- Month Salary Total --}}
        <div class="bg-white dark:bg-[#111827] rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-yellow-100 dark:bg-yellow-900/50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">G Bulan Ini</p>
                    <p class="text-xl font-semibold text-gray-900 dark:text-white">Rp {{ number_format($monthSalaries, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

{{-- Recent Activity --}}
<div class="bg-white dark:bg-[#111827] rounded-xl border border-gray-200 dark:border-gray-800 p-4 mb-6 shadow-lg">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aktivitas Terbaru</h3>
    @if($recentAttendances->count() > 0)
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-[#1f2937]/50 border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-3 font-semibold tracking-wider">Trainer</th>
                        <th class="px-4 py-3 font-semibold tracking-wider">Murid</th>
                        <th class="px-4 py-3 font-semibold tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 font-semibold tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                    @foreach($recentAttendances as $attendance)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $attendance->trainer->name ?? 'Tanpa Nama' }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $attendance->student->name ?? 'Tanpa Nama' }}</td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                @if($attendance->status === 'Hadir')
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-400">Hadir</span>
                                @elseif($attendance->status === 'Izin')
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-400">Izin</span>
                                @elseif($attendance->status === 'Sakit')
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-400">Sakit</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">Alpha</span>
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

{{-- Active Trainers --}}
<div class="bg-white dark:bg-[#111827] rounded-xl border border-gray-200 dark:border-gray-800 p-4 shadow-lg">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Daftar Trainer Aktif</h3>
    @if($trainers->count() > 0)
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-[#1f2937]/50 border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-3 font-semibold tracking-wider">Nama</th>
                        <th class="px-4 py-3 font-semibold tracking-wider">Role</th>
                        <th class="px-4 py-3 font-semibold tracking-wider">No. HP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                    @foreach($trainers as $trainer)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $trainer->name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400">{{ $trainer->role }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $trainer->phone ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-500 dark:text-gray-400 text-center py-4">Belum ada trainer aktif.</p>
    @endif
</div>
</div>
@endsection
