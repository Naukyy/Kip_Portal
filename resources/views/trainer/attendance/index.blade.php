@extends('layouts.app')
@section('title', 'Presensi Harian')

@push('styles')
<style>
/* ── Session Box Glow on hover ───────────────────────── */
.session-box {
    position: relative;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.session-box:hover {
    transform: translateY(-2px);
}
.session-box.status-pending:hover {
    box-shadow: 0 0 20px rgba(59, 130, 246, 0.25);
}
.session-box.status-in_progress {
    box-shadow: 0 0 0 2px #22c55e, 0 0 16px rgba(34, 197, 94, 0.35);
    animation: pulse-green 2s infinite;
}
.session-box.status-completed {
    opacity: 0.75;
}

@keyframes pulse-green {
    0%, 100% { box-shadow: 0 0 0 2px #22c55e, 0 0 16px rgba(34, 197, 94, 0.3); }
    50%       { box-shadow: 0 0 0 2px #22c55e, 0 0 30px rgba(34, 197, 94, 0.6); }
}

/* ── Neon "Mulai Kelas" button ───────────────────────── */
.btn-start {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}
.btn-start::before {
    content: '';
    position: absolute;
    inset: -2px;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6, #06b6d4);
    border-radius: inherit;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: -1;
}
.btn-start:hover::before { opacity: 1; }
.btn-start:hover {
    color: #fff;
    box-shadow: 0 0 18px rgba(99, 102, 241, 0.6);
    transform: scale(1.03);
}

/* ── Date pill ───────────────────────────────────────── */
.date-pill {
    background: linear-gradient(135deg, rgba(59,130,246,0.15), rgba(139,92,246,0.15));
    border: 1px solid rgba(99,102,241,0.3);
}

/* ── Empty state ────────────────────────────────────── */
.empty-wave {
    animation: float 3s ease-in-out infinite;
}
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-6px); }
}
</style>
@endpush

@section('content')
<div class="p-4 md:p-8 max-w-7xl mx-auto w-full">

    {{-- ── Header Row ──────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                📅 Jadwal Harian
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Pilih tanggal untuk melihat sesi kelas Anda
            </p>
        </div>

        {{-- Date Picker ──────────────────────────── --}}
        <form method="GET" id="date-form" class="flex items-center gap-3">
            <div class="date-pill flex items-center gap-3 px-4 py-2.5 rounded-2xl">
                <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <input type="date" name="date" id="date-input"
                       value="{{ $date->toDateString() }}"
                       class="bg-transparent border-0 text-sm font-medium text-gray-800 dark:text-white focus:ring-0 outline-none p-0 cursor-pointer"
                       onchange="document.getElementById('date-form').submit()">
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400 hidden sm:block">
                {{ $date->locale('id')->translatedFormat('l, d F Y') }}
            </span>
        </form>
    </div>

    {{-- ── Day Badge ───────────────────────────────── --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="px-4 py-1.5 rounded-full text-xs font-semibold
                    bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300
                    border border-primary-200 dark:border-primary-800 uppercase tracking-widest">
            {{ $dayName }}
        </div>
        <div class="h-px flex-1 bg-gradient-to-r from-gray-200 dark:from-gray-700 to-transparent"></div>
    </div>

    {{-- ── Session Grid ─────────────────────────────── --}}
    @if($sessionGroups->isEmpty())
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="empty-wave text-6xl mb-4">🗓️</div>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Tidak ada jadwal pada hari ini
            </h3>
            <p class="text-sm text-gray-400 dark:text-gray-500 max-w-xs">
                Coba pilih tanggal lain, atau hubungi admin untuk menambah jadwal Anda.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($sessionGroups as $sessionTime => $students)
                @php
                    $meeting = $meetings[$sessionTime];
                    $statusClass = match($meeting->status) {
                        'in_progress' => 'status-in_progress',
                        'completed'   => 'status-completed',
                        default       => 'status-pending',
                    };
                @endphp

                {{-- Session Box ────────────────────── --}}
                <div class="session-box {{ $statusClass }}
                            bg-white dark:bg-[#111827]
                            rounded-2xl border border-gray-200 dark:border-gray-800
                            shadow-lg p-5 flex flex-col gap-4">

                    {{-- Box Header --}}
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 font-medium uppercase tracking-widest mb-1">
                                Sesi
                            </p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ $sessionTime ?: '—' }}
                            </p>
                        </div>

                        {{-- Status Badge --}}
                        @if($meeting->status === 'in_progress')
                            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                         bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                Berlangsung
                            </span>
                        @elseif($meeting->status === 'completed')
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                         bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                ✓ Selesai
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                         bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                Belum Mulai
                            </span>
                        @endif
                    </div>

                    {{-- Student Count --}}
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>{{ $students->count() }} murid</span>
                    </div>

                    {{-- Student Avatars --}}
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($students->take(5) as $student)
                            <div title="{{ $student->name }}"
                                 class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-400 to-primary-600
                                        flex items-center justify-center text-white text-xs font-semibold shadow-sm">
                                {{ strtoupper(substr($student->name, 0, 2)) }}
                            </div>
                        @endforeach
                        @if($students->count() > 5)
                            <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800
                                        flex items-center justify-center text-xs text-gray-500 dark:text-gray-400 font-semibold">
                                +{{ $students->count() - 5 }}
                            </div>
                        @endif
                    </div>

                    {{-- Divider --}}
                    <div class="h-px bg-gray-100 dark:bg-gray-800"></div>

                    {{-- Action Button --}}
                    @if($meeting->status === 'completed')
                        <a href="{{ route('trainer.attendance.show', $meeting) }}"
                           class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl
                                  bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400
                                  text-sm font-medium transition-colors hover:bg-gray-200 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Lihat Rekap
                        </a>
                    @elseif($meeting->status === 'in_progress')
                        <a href="{{ route('trainer.attendance.show', $meeting) }}"
                           class="btn-start flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl
                                  bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400
                                  border border-green-300 dark:border-green-700
                                  text-sm font-semibold z-10">
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            Lanjutkan Presensi
                        </a>
                    @else
                        <a href="{{ route('trainer.attendance.show', $meeting) }}"
                           class="btn-start flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl
                                  bg-white dark:bg-[#1a2235]
                                  border border-gray-300 dark:border-gray-700
                                  text-gray-700 dark:text-gray-300
                                  text-sm font-semibold z-10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Mulai Kelas
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
