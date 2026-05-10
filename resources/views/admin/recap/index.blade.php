@extends('layouts.app')

@section('title', 'Rekap Absensi Bulanan')

@section('content')
<div class="p-4 md:p-8 max-w-7xl mx-auto w-full">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Rekap Absensi Bulanan (Admin)</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Rekap hasil absensi murid selama 1 bulan.
            </p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('admin.recap.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">

            {{-- Trainer --}}
            <div>
                <x-input-label for="trainer_id" :value="'Trainer'" />
                <select id="trainer_id" name="trainer_id"
                        class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm px-3 py-2">
                    <option value="" {{ empty($trainerId) ? 'selected' : '' }}>Semua trainer</option>
                    @foreach($trainers as $t)
                        <option value="{{ $t->id }}" {{ (string)$trainerId === (string)$t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Month --}}
            <div>
                <x-input-label for="month" :value="'Bulan'" />
                <select id="month" name="month"
                        class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm px-3 py-2">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ (int)$month === $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate($year, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Year --}}
            <div>
                <x-input-label for="year" :value="'Tahun'" />
                <select id="year" name="year"
                        class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm px-3 py-2">
                    @php
                        $currentYear = \Carbon\Carbon::now()->year;
                    @endphp
                    @for($y = $currentYear - 3; $y <= $currentYear + 1; $y++)
                        <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Session --}}
            <div>
                <x-input-label for="session" :value="'Sesi'" />
                <select id="session" name="session"
                        class="mt-1 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm px-3 py-2">
                    <option value="" {{ empty($session) ? 'selected' : '' }}>Semua sesi</option>
@foreach($sessions as $st)
                        <option value="{{ $st }}" {{ (string)($session ?? '') === (string)$st ? 'selected' : '' }}>
                            {{ $st }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex gap-2">
                <button type="submit"
                        class="w-full md:w-auto px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold transition">
                    Tampilkan
                </button>
                <a href="{{ route('admin.recap.index', ['month'=>$month,'year'=>$year]) }}"
                   class="w-full md:w-auto px-5 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-semibold transition text-center">
                    Reset
                </a>
            </div>

        </form>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mt-3">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Menampilkan absensi semua murid aktif.
            </p>

            {{-- Export --}}
            <div>
                @php
                    $query = array_filter([
                        'trainer_id' => $trainerId,
                        'session' => $session,
                        'month' => $month,
                        'year' => $year,
                    ], fn($v) => $v !== null && $v !== '');
                @endphp
                <a href="{{ route('admin.recap.export', $query) }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Export CSV
                </a>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-auto">

        @if($students->isEmpty())
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                Tidak ada murid aktif.
            </div>
        @else
            <table class="min-w-[1100px] w-full border-collapse">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="text-left text-xs font-semibold text-gray-600 dark:text-gray-300 px-4 py-3 border-b border-gray-200 dark:border-gray-800">ID / Nama</th>
                        <th class="text-left text-xs font-semibold text-gray-600 dark:text-gray-300 px-4 py-3 border-b border-gray-200 dark:border-gray-800">Trainer</th>

                        @for($d = 1; $d <= $daysInMonth; $d++)
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

                            <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-800">
                                {{ $student->trainer->name ?? '-' }}
                            </td>

                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $a = $attByDay->get($d);
                                    $status = $a?->status;

                                    // Normalisasi status
                                    $s = is_string($status) ? strtolower(trim($status)) : null;

                                    // Mapping yang umum di aplikasi ini
                                    $letterMap = [
                                        'hadir' => 'H',
                                        'attend' => 'H',
                                        'alpha' => 'A',
                                        'absent' => 'A',
                                        'izin' => 'I',
                                        'permission' => 'I',
                                        'sakit' => 'S',
                                    ];

                                    $badgeClassesMap = [
                                        'hadir' => 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 border border-green-300 dark:border-green-700',
                                        'attend' => 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 border border-green-300 dark:border-green-700',

                                        'alpha' => 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border border-red-300 dark:border-red-700',
                                        'absent' => 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border border-red-300 dark:border-red-700',

                                        'izin' => 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300 border border-yellow-300 dark:border-yellow-700',
                                        'permission' => 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300 border border-yellow-300 dark:border-yellow-700',

                                        'sakit' => 'bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300 border border-orange-300 dark:border-orange-700',
                                    ];

                                    $letter = ($s && isset($letterMap[$s])) ? $letterMap[$s] : '-';
                                    $badge = ($s && isset($badgeClassesMap[$s])) ? $badgeClassesMap[$s] : 'bg-gray-100 dark:bg-gray-800/40 text-gray-400';

                                    if ($s === 'hadir' || $s === 'attend') {
                                        $hadirCount++;
                                    }
                                @endphp

                                <td class="text-center px-2 py-2 border-b border-gray-200 dark:border-gray-800">
                                    @if(!$status || $s === 'pending')
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-bold text-gray-400 bg-gray-100 dark:bg-gray-800/40">-</span>
                                    @else
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-xs font-bold {{ $badge }}">{{ $letter }}</span>
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

