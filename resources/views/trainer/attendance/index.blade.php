@extends('layouts.app')
@section('title', 'Presensi Harian')

@section('content')
<div class="space-y-6" x-data="attendanceForm()">

    {{-- Date picker --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date->toDateString() }}"
                       class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm"
                       onchange="this.form.submit()">
            </div>
            <p class="text-sm text-gray-500">
                {{ $date->translatedFormat('l, d F Y') }}
            </p>
        </form>
    </div>

    {{-- Attendance Form --}}
    <form method="POST" action="{{ route('trainer.attendance.store') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $date->toDateString() }}">

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">
                    Daftar Murid — {{ $students->count() }} siswa
                </h2>
                <button type="submit"
                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Simpan Presensi
                </button>
            </div>

            @if($students->isEmpty())
                <div class="px-6 py-12 text-center text-gray-400">
                    Belum ada siswa yang terdaftar.
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        <tr>
                            <th class="px-6 py-3 text-left">Nama Siswa</th>
                            <th class="px-6 py-3 text-left">Sesi</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-left">Pengganti (jika cover)</th>
                            <th class="px-6 py-3 text-left">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($students as $student)
                        @php
                            $existing = $existingAttendances[$student->id] ?? null;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $student->name }}</p>
                                <p class="text-xs text-gray-400">{{ $student->student_id }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $student->session_time }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-1">
                                    @foreach(['Attend' => ['H', 'green'], 'Permission' => ['I', 'yellow'], 'Absent' => ['A', 'red']] as $status => [$label, $color])
                                    <label class="cursor-pointer">
                                        <input type="radio"
                                               name="attendances[{{ $student->id }}][status]"
                                               value="{{ $status }}"
                                               {{ ($existing?->status ?? 'Attend') === $status ? 'checked' : '' }}
                                               class="sr-only peer">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg border text-xs font-bold
                                                     border-gray-300 dark:border-gray-700 text-gray-400
                                                     peer-checked:bg-{{ $color }}-100 dark:peer-checked:bg-{{ $color }}-900
                                                     peer-checked:border-{{ $color }}-400
                                                     peer-checked:text-{{ $color }}-700 dark:peer-checked:text-{{ $color }}-300
                                                     transition-all">{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <select name="attendances[{{ $student->id }}][substitute_id]"
                                        class="text-xs rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 w-full">
                                    <option value="">— (saya sendiri)</option>
                                    @foreach($otherTrainers as $t)
                                        <option value="{{ $t->id }}"
                                            {{ $existing?->substitute_trainer_id == $t->id ? 'selected' : '' }}>
                                            {{ $t->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-6 py-4">
                                <input type="text"
                                       name="attendances[{{ $student->id }}][notes]"
                                       value="{{ $existing?->notes }}"
                                       placeholder="Catatan..."
                                       class="text-xs rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 w-full">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </form>
</div>
@endsection