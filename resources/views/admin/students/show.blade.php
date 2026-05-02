@extends('admin.layouts.app')

@section('title', 'Detail Siswa - ' . $student->name)

@section('content')
<div class="p-6 md:p-8 max-w-5xl mx-auto w-full">
    <!-- Header with Back Button -->
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.students.index') }}" class="flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">{{ $student->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $student->student_id }}</p>
        </div>
        <div class="ml-auto">
            @if($student->is_active)
                <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-sm font-medium rounded-full">Aktif</span>
            @else
                <span class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-sm font-medium rounded-full">Nonaktif</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Student Details Card -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-[#111827] rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Siswa</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nama Lengkap</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">ID Siswa</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->student_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Periode</label>
                        <p class="text-gray-900 dark:text-white">{{ $student->periode ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Jadwal</label>
                        <p class="text-gray-900 dark:text-white">{{ $student->schedule ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Sesi</label>
                        <p class="text-gray-900 dark:text-white">{{ $student->session_time ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Telepon</label>
                        <p class="text-gray-900 dark:text-white">{{ $student->phone ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                        <p class="text-gray-900 dark:text-white">{{ $student->email ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Alamat</label>
                        <p class="text-gray-900 dark:text-white">{{ $student->address ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Parent Info Card -->
            <div class="bg-white dark:bg-[#111827] rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Orang Tua</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nama Orang Tua</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->parent_name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Telepon Orang Tua</label>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $student->parent_phone ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Notes Card -->
            @if($student->notes)
            <div class="bg-white dark:bg-[#111827] rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Catatan</h2>
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $student->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Sidebar - Trainer Info -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-[#111827] rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Trainer</h2>
                @if($student->trainer)
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 dark:text-blue-400 font-semibold text-lg">
                                {{ substr($student->trainer->name, 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-gray-900 dark:text-white font-medium">{{ $student->trainer->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $student->trainer->role }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400">Belum ada trainer</p>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-[#111827] rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aksi Cepat</h2>
                <div class="space-y-2">
                    <a href="{{ route('admin.students.index', ['trainer_id' => $student->trainer_id]) }}" class="block w-full px-4 py-2 text-center bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                        Lihat Siswa Trainer Ini
                    </a>
                    <form action="{{ route('admin.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus siswa ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="block w-full px-4 py-2 text-center bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 rounded-lg transition-colors">
                            Hapus Siswa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance History -->
    <div class="mt-6 bg-white dark:bg-[#111827] rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Absensi (30 Hari Terakhir)</h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $student->attendances->count() }} record</span>
        </div>
        
        @if($student->attendances->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($student->attendances as $attendance)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3">
                                    {{ \Carbon\Carbon::parse($attendance->date)->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($attendance->status === 'Hadir')
                                        <span class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-medium rounded">Hadir</span>
                                    @elseif($attendance->status === 'Sakit')
                                        <span class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs font-medium rounded">Sakit</span>
                                    @elseif($attendance->status === 'Izin')
                                        <span class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 text-xs font-medium rounded">Izin</span>
                                    @else
                                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium rounded">{{ $attendance->status }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                    {{ $attendance->notes ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 dark:text-gray-400 text-center py-8">Belum ada data absensi.</p>
        @endif
    </div>
</div>
@endsection
