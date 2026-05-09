@extends('layouts.app')
@section('title', 'Rekap Absensi Bulanan')

@push('styles')
<style>
    /* simple fallback if Tailwind primary classes not configured */
    .bg-primary-50{background:#eef2ff;}
    .bg-primary-600{background:#4f46e5;}
    .hover\:bg-primary-700:hover{background:#4338ca;}
</style>
@endpush

@section('content')
<div class="p-4 md:p-8 max-w-7xl mx-auto w-full">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Rekap Bulanan</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Rekap hasil absensi murid selama 1 bulan.
            </p>
        </div>

        <a href="{{ route('trainer.attendance.index') }}"
           class="px-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
            ← Kembali ke Presensi Harian
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('trainer.attendance.recap') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">

            <div>
                <x-input-label for="month" :value="'Bulan'" />
                <select id="month" name="month"
                        class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm px-3 py-2">
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ $m }}" {{ (int)$month === $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate($year, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <x-input-label for="year" :value="'Tahun'" />
                <select id="year" name="year"
                        class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm px-3 py-2">
                    @for($y = $currentYear - 3; $y <= $currentYear + 1; $y++)
                        <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <x-input-label for="session_time" :value="'Sesi'" />
                <select id="session_time" name="session_time"
                        class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm px-3 py-2">
                    <option value="" {{ empty($selectedSessionTime) ? 'selected' : '' }}>Semua sesi</option>
                    @foreach($sessions as $st)
                        <option value="{{ $st }}" {{ (string)$selectedSessionTime === (string)$st ? 'selected' : '' }}>
                            {{ $st }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="w-full md:w-auto px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold transition">
                    Tampilkan
                </button>
                <a href="{{ route('trainer.attendance.recap', ['month'=>$month,'year'=>$year]) }}"
                   class="w-full md:w-auto px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-semibold transition text-center">
                    Reset
                </a>
            </div>

        </form>

        <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
            Menampilkan absensi untuk trainer Anda pada bulan & tahun terpilih.
        </p>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-auto">
        @if($students->isEmpty())
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                Tidak ada murid aktif.
            </div>
        @else
            <table class="min-w-[900px] w-full border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-600 dark:text-gray-300 px-4 py-3 border-b border-gray-200 dark:border-gray-800">ID / Nama</th>
                        @for($d=1; $d <= $daysInMonth; $d++)
                            <th class="text-center text-xs font-semibold text-gray-600 dark:text-gray-300 px-2 py-3 border-b border-gray-200 dark:border-gray-800">{{ $d }}</th>
                        @endfor
                        <th class="text-center text-xs font-semibold text-gray-600 dark:text-gray-300 px-4 py-3 border-b border-gray-200 dark:border-gray-800">Total Hadir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        @php
                            $attByDay = $student->attendances->keyBy(fn($a) => $a->date->day);
                            $hadirCount = 0;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                            <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                                <div class="font-semibold text-gray-900 dark:text-white text-sm">{{ $student->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $student->student_id }}</div>
                            </td>

                            @for($d=1; $d <= $daysInMonth; $d++)
                                @php
                                    $a = $attByDay->get($d);
                                    $status = $a?->status;
                                @endphp
                                <td class="text-center px-2 py-2 border-b border-gray-200 dark:border-gray-800">
                                    @if(!$status || $status === 'pending')
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-800/40">-</span>
                                    @else
                                        @php
                                            $badgeMap = [
                                                'hadir' => 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 border border-green-300 dark:border-green-700',
                                                'alpha' => 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border border-red-300 dark:border-red-700',
                                                'sakit' => 'bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300 border border-orange-300 dark:border-orange-700',
                                                'izin'  => 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300 border border-yellow-300 dark:border-yellow-700',
                                            ];
                                            $classes = $badgeMap[$status] ?? 'bg-gray-100 dark:bg-gray-800/40 text-gray-400';
                                            $letterMap = ['hadir'=>'H','alpha'=>'A','sakit'=>'S','izin'=>'I'];
                                            $letter = $letterMap[$status] ?? '-';
                                            if($status === 'hadir') $hadirCount++;
                                        @endphp
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-bold {{ $classes }}">{{ $letter }}</span>
                                    @endif
                                </td>
                            @endfor

                            <td class="text-center px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-xl bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-bold">
                                    {{ $hadirCount }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>
@endsection

