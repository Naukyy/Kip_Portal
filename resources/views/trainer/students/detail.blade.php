@extends('layouts.app')

@section('title', 'Detail Siswa - {{ $student->name }}')

@section('content')
<div class="p-6 md:p-8 max-w-5xl mx-auto w-full">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('trainer.students.index') }}" class="flex items-center justify-center w-12 h-12 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition-all shadow-sm">
            <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $student->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ $student->student_id }}</p>
            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $student->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400' }}">
                {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-gray-800 p-8 shadow-lg">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-200 dark:border-gray-700 pb-4">Informasi Siswa</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Sesi</label>
                        <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $student->session_time ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Jadwal</label>
                        <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $student->schedule ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Telepon</label>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $student->phone) }}" target="_blank" class="text-xl font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            {{ $student->phone ?? '-' }}
                        </a>
                    </div>
                    @if($student->email)
                    <div>
                        <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Email</label>
                        <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $student->email }}</p>
                    </div>
                    @endif
                    @if($student->periode)
                    <div>
                        <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Periode</label>
                        <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $student->periode }}</p>
                    </div>
                    @endif
                    @if($student->address)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Alamat</label>
                        <p class="text-lg text-gray-900 dark:text-white">{{ $student->address }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Parent Info -->
            @if($student->parent_name || $student->parent_phone)
            <div class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-gray-800 p-8 shadow-lg">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-200 dark:border-gray-700 pb-4">Informasi Orang Tua</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($student->parent_name)
                    <div>
                        <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Nama</label>
                        <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ $student->parent_name }}</p>
                    </div>
                    @endif
                    @if($student->parent_phone)
                    <div>
                        <label class="block text-sm font-semibold text-gray-500 dark:text-gray-400 mb-2">Telepon</label>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $student->parent_phone) }}" target="_blank" class="text-xl font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            {{ $student->parent_phone }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Trainer Sidebar (Current Trainer Info) -->
        <div>
            <div class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-gray-800 p-8 shadow-lg sticky top-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Trainer Pembimbing</h3>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center">
                        <span class="text-blue-600 dark:text-blue-400 font-bold text-lg">
                            {{ substr(auth()->user()->name, 0, 2) }}
                        </span>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->role }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attendance -->
    <div class="mt-12 bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-gray-800 p-8 shadow-lg">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 border-b border-gray-200 dark:border-gray-700 pb-4 flex items-center gap-2">
            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Riwayat Absensi (30 Hari Terakhir)
        </h2>
        
        @if($student->attendances->count() > 0)
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-[#1f2937]/50 text-xs uppercase text-gray-500 dark:text-gray-400">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Tanggal</th>
                        <th class="px-6 py-4 text-left font-semibold">Status</th>
                        <th class="px-6 py-4 text-left font-semibold">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                    @foreach($student->attendances as $attendance)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            @php $status = $attendance->status; @endphp
                            @if($status === 'Hadir')
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full dark:bg-green-900/50 dark:text-green-400">Hadir</span>
                            @elseif($status === 'Sakit')
                                <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full dark:bg-red-900/50 dark:text-red-400">Sakit</span>
                            @elseif($status === 'Izin')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full dark:bg-yellow-900/50 dark:text-yellow-400">Izin</span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded-full dark:bg-gray-800 dark:text-gray-400">Alpha</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                            {{ $attendance->notes ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-lg text-gray-500 dark:text-gray-400">Belum ada riwayat absensi</p>
        </div>
        @endif
    </div>
</div>
@endsection

