@extends('layouts.app')
@section('title', 'Presensi — ' . $meeting->session_time)

@push('styles')
<style>
.student-row { transition: all 0.35s cubic-bezier(0.4,0,0.2,1); }
.student-row.leaving { opacity:0; transform:translateX(40px) scale(0.95); }
.student-row.entering { animation: slideIn 0.35s ease forwards; }
@keyframes slideIn {
  from { opacity:0; transform:translateX(-30px) scale(0.95); }
  to   { opacity:1; transform:none; }
}
.btn-status { transition: all .2s ease; }
.btn-status:hover { transform:scale(1.08); box-shadow:0 0 12px rgba(99,102,241,.45); }
.overlay-locked { backdrop-filter: blur(4px); background: rgba(0,0,0,0.45); }
.btn-start-class {
  background: linear-gradient(135deg,#3b82f6,#8b5cf6);
  box-shadow: 0 0 30px rgba(99,102,241,.5);
  transition: all .3s ease;
}
.btn-start-class:hover { transform:scale(1.04); box-shadow:0 0 50px rgba(99,102,241,.75); }
.badge-hadir  { @apply bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 border border-green-300 dark:border-green-700; }
.badge-alpha  { @apply bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border border-red-300 dark:border-red-700; }
.badge-sakit  { @apply bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300 border border-orange-300 dark:border-orange-700; }
.badge-izin   { @apply bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300 border border-yellow-300 dark:border-yellow-700; }
</style>
@endpush

@section('content')
<div class="p-4 md:p-6 max-w-7xl mx-auto w-full"
     x-data="attendanceSPA()"
     x-init="init()">

  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div class="flex items-center gap-3">
      <a href="{{ route('trainer.attendance.index', ['date'=>$date->toDateString()]) }}"
         class="p-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 hover:text-gray-800 dark:hover:text-white transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      </a>
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Presensi Kelas</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $meeting->session_time }} · {{ $date->locale('id')->translatedFormat('l, d F Y') }}</p>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <span x-text="statusLabel()" :class="statusBadgeClass()" class="px-3 py-1 rounded-full text-xs font-semibold transition-all duration-500"></span>
      <span x-show="(classState || '')==='in_progress'" class="text-xs text-gray-400 dark:text-gray-500">Mulai: <span x-text="startedAt"></span></span>
    </div>
  </div>

  <div x-show="classState==='pending'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="relative">
    <div class="filter blur-sm pointer-events-none select-none bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
      <div class="space-y-3">
        @foreach($students as $s)
        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50">
          <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($s->name,0,2)) }}</div>
          <div class="flex-1">
            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $s->name }}</p>
            <p class="text-xs text-gray-400">{{ $s->student_id }}</p>
          </div>
          <div class="flex gap-1.5">
            @foreach(['H','A','S','I'] as $l)
            <div class="w-8 h-8 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
            @endforeach
          </div>
        </div>
        @endforeach
      </div>
    </div>

    <div class="absolute inset-0 overlay-locked rounded-2xl flex flex-col items-center justify-center gap-5 z-10">
      <div class="text-center">
        <p class="text-white/80 text-sm mb-1">{{ $students->count() }} murid terdaftar</p>
        <p class="text-white font-semibold text-lg">Kelas siap dimulai</p>
      </div>
      <button @click="startClass()" :disabled="starting" class="btn-start-class px-8 py-4 rounded-2xl text-white font-bold text-base flex items-center gap-3 disabled:opacity-60 disabled:cursor-not-allowed">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span x-text="starting ? 'Memulai...' : 'Start Class'"></span>
      </button>
    </div>
  </div>

  <div x-show="classState!=='pending'" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    <div class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-lg overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
        <div class="flex items-center gap-2"><div class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></div><h3 class="font-semibold text-gray-900 dark:text-white text-sm">Belum Diabsen</h3></div>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400" x-text="pendingStudents.length + ' siswa'"></span>
      </div>

      <div class="p-4 space-y-2 min-h-[200px]">
        <template x-if="pendingStudents.length === 0">
          <div class="flex flex-col items-center justify-center py-10 text-center text-gray-400 dark:text-gray-600">
            <svg class="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm">Semua siswa sudah diabsen!</p>
          </div>
        </template>

        <template x-for="s in pendingStudents" :key="s.id">
          <div class="student-row flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0" x-text="s.initials"></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="s.name"></p>
              <p class="text-xs text-gray-400" x-text="s.student_id"></p>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0" x-show="classState==='in_progress'">
              <button @click="setStatus(s, 'hadir')" class="btn-status w-8 h-8 rounded-lg text-xs font-bold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-300 dark:border-green-700" title="Hadir">H</button>
              <button @click="setStatus(s, 'alpha')" class="btn-status w-8 h-8 rounded-lg text-xs font-bold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-300 dark:border-red-700" title="Alpha">A</button>
              <button @click="openMoveModal(s, 'sakit')" class="btn-status w-8 h-8 rounded-lg text-xs font-bold bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 border border-orange-300 dark:border-orange-700" title="Sakit">S</button>
              <button @click="openMoveModal(s, 'izin')" class="btn-status w-8 h-8 rounded-lg text-xs font-bold bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 border border-yellow-300 dark:border-yellow-700" title="Izin">I</button>
            </div>
          </div>
        </template>
      </div>
    </div>

    <div class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-lg overflow-hidden flex flex-col">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
        <div class="flex items-center gap-2"><div class="w-2.5 h-2.5 rounded-full bg-green-500"></div><h3 class="font-semibold text-gray-900 dark:text-white text-sm">Sudah Diabsen</h3></div>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400" x-text="doneStudents.length + ' siswa'"></span>
      </div>

      <div class="p-4 space-y-2 min-h-[200px] flex-1">
        <template x-if="doneStudents.length === 0">
          <div class="flex flex-col items-center justify-center py-10 text-center text-gray-400 dark:text-gray-600">
            <p class="text-sm">Belum ada yang diabsen</p>
          </div>
        </template>

        <template x-for="s in doneStudents" :key="s.id">
          <div class="student-row entering flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0" x-text="s.initials"></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="s.name"></p>
              <p class="text-xs text-gray-400" x-text="s.student_id"></p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
              <span class="px-2 py-0.5 rounded-full text-xs font-semibold" x-bind:class="'badge-' + s.status" x-text="statusLabels[s.status]"></span>
              <button x-show="classState==='in_progress'" @click="undoStatus(s)" class="p-1 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Undo">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
              </button>
            </div>
          </div>
        </template>
      </div>

      <div x-show="classState==='in_progress'" class="p-4 border-t border-gray-100 dark:border-gray-800">
        <button @click="confirmEnd=true" :disabled="pendingStudents.length > 0" class="w-full py-3 rounded-xl font-semibold text-sm text-white transition-all duration-200 flex items-center justify-center gap-2"
                :class="pendingStudents.length === 0 ? 'bg-gradient-to-r from-red-500 to-pink-600 hover:shadow-lg hover:shadow-red-500/30 hover:scale-[1.02]' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed'">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10h6v4H9z"/></svg>
          <span x-text="pendingStudents.length > 0 ? pendingStudents.length + ' siswa belum diabsen' : 'End Class'"></span>
        </button>
      </div>

      <div x-show="classState==='completed'" class="p-4 border-t border-gray-100 dark:border-gray-800">
        <div class="flex items-center gap-2 justify-center text-green-600 dark:text-green-400 text-sm font-semibold">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Kelas selesai — Data dikunci
        </div>
        <a href="{{ route('trainer.attendance.index', ['date'=>$date->toDateString()]) }}" class="mt-3 flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 text-sm font-medium hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors">
          ← Kembali ke Jadwal
        </a>
      </div>
    </div>
  </div>

  {{-- Move Modal --}}
  <div x-show="moveModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
    <div @click.stop class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xl p-6 max-w-md w-full" x-transition>
      <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/40 flex items-center justify-center">
          <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <h4 class="font-bold text-gray-900 dark:text-white">Pindahkan Presensi</h4>
          <p class="text-xs text-gray-500 dark:text-gray-400">Untuk murid: <span x-text="moveStudent && moveStudent.name"></span></p>
        </div>
      </div>

      <div class="space-y-4">
        <div>
          <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Status</label>
          <div class="mt-1 flex gap-2">
            <button type="button" @click="moveStatusValue='sakit'" class="flex-1 px-3 py-2 rounded-xl border text-sm font-semibold transition-colors"
                    x-bind:class="moveStatusValue==='sakit'
                      ? 'bg-orange-50 dark:bg-orange-900/30 border-orange-300 dark:border-orange-700 text-orange-700 dark:text-orange-300'
                      : 'bg-white dark:bg-[#111827] border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300'">
              Sakit
            </button>
            <button type="button" @click="moveStatusValue='izin'" class="flex-1 px-3 py-2 rounded-xl border text-sm font-semibold transition-colors"
                    x-bind:class="moveStatusValue==='izin'
                      ? 'bg-yellow-50 dark:bg-yellow-900/30 border-yellow-300 dark:border-yellow-700 text-yellow-700 dark:text-yellow-300'
                      : 'bg-white dark:bg-[#111827] border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300'">
              Izin
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Tanggal Target</label>
            <input type="date" class="mt-1 w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-[#111827] text-sm text-gray-900 dark:text-white" x-model="moveTargetDate" />
          </div>
          <div>
            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Sesi Target</label>
            <select class="mt-1 w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-[#111827] text-sm text-gray-900 dark:text-gray-900" x-model="moveTargetSessionTime">
              <template x-for="t in moveAvailableSessionTimes" :key="t">
                <option :value="t" x-text="t"></option>
              </template>
            </select>
          </div>
        </div>

        <div class="rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/30 p-3">
          <div class="text-xs font-semibold text-gray-700 dark:text-gray-200">Catatan</div>
          <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">Jika pada tanggal/sesi target murid sudah memiliki presensi dengan status selain <b>pending</b>, pemindahan akan ditolak.</div>
        </div>
      </div>

      <div class="mt-5 flex gap-3">
        <button type="button" @click="moveModal=false" class="flex-1 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Batal</button>
        <button type="button" @click="submitMove()" :disabled="moveSubmitting" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-yellow-500 to-amber-600 text-white text-sm font-semibold hover:opacity-90 transition-opacity disabled:opacity-60 disabled:cursor-not-allowed">
          <span x-text="moveSubmitting ? 'Memproses...' : 'Pindahkan'"></span>
        </button>
      </div>

    </div>
  </div>

  {{-- Confirm End Modal --}}
  <div x-show="confirmEnd" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
    <div @click.stop class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-gray-700 shadow-2xl p-6 max-w-sm w-full" x-transition>
      <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
          <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
        </div>
        <div>
          <h4 class="font-bold text-gray-900 dark:text-white">Akhiri Kelas?</h4>
          <p class="text-xs text-gray-500 dark:text-gray-400">Data presensi akan dikunci</p>
        </div>
      </div>
      <p class="text-sm text-gray-600 dark:text-gray-400 mb-5">Apakah Anda yakin ingin mengakhiri kelas? Setelah dikonfirmasi, data presensi tidak dapat diubah.</p>
      <div class="flex gap-3">
        <button @click="confirmEnd=false" class="flex-1 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Batal</button>
        <button @click="endClass()" :disabled="ending" class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-pink-600 text-white text-sm font-semibold hover:opacity-90 transition-opacity disabled:opacity-60 disabled:cursor-not-allowed"><span x-text="ending ? 'Memproses...' : 'Ya, Akhiri'"></span></button>
      </div>
    </div>
  </div>

  <div x-show="errorMsg" x-transition @click="errorMsg=''" class="fixed bottom-5 right-5 z-50 px-4 py-3 bg-red-600 text-white rounded-xl shadow-lg text-sm font-medium cursor-pointer max-w-xs" x-text="errorMsg"></div>

</div>
@endsection

@push('scripts')
<script>
function attendanceSPA() {
  const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;

  return {
    classState: @json($meeting->status),
    starting: false,
    ending: false,
    confirmEnd: false,
    errorMsg: '',
    startedAt: @json($meeting->started_at ?? ''),

    moveModal: false,
    moveStudent: null,
    moveStatusValue: 'izin',
    moveTargetDate: @json($date->toDateString()),
    moveTargetSessionTime: @json($meeting->session_time),
    moveAvailableSessionTimes: [],
    moveSubmitting: false,

    statusLabels: { hadir: 'Hadir', alpha: 'Alpha', sakit: 'Sakit', izin: 'Izin', pending: 'Pending' },
    allStudents: @json($studentData),

    get pendingStudents() { return this.allStudents.filter(s => s.status === 'pending'); },
    get doneStudents() { return this.allStudents.filter(s => s.status !== 'pending'); },

    statusLabel() {
      return { pending: 'Belum Mulai', in_progress: '🟢 Berlangsung', completed: '✓ Selesai' }[this.classState] ?? '';
    },
    statusBadgeClass() {
      return {
        pending: 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full px-3 py-1 text-xs font-semibold',
        in_progress: 'bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full px-3 py-1 text-xs font-semibold',
        completed: 'bg-gray-100 dark:bg-gray-800 text-gray-500 rounded-full px-3 py-1 text-xs font-semibold'
      }[this.classState] ?? '';
    },

    init() {
      this.moveAvailableSessionTimes = [this.moveTargetSessionTime];
    },

    openMoveModal(student, status) {
      this.moveStudent = student;
      this.moveStatusValue = status;
      this.moveModal = true;
      this.errorMsg = '';
    },

    async submitMove() {
      this.moveSubmitting = true;
      this.errorMsg = '';
      try {
        const res = await fetch('{{ route("trainer.attendance.move-status", $meeting) }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            student_id: this.moveStudent.id,
            status: this.moveStatusValue,
            target_date: this.moveTargetDate,
            target_session_time: this.moveTargetSessionTime
          })
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
          this.errorMsg = data.message ?? 'Gagal memindahkan presensi.';
          return;
        }

        this.moveStudent.status = this.moveStatusValue;
        this.moveModal = false;
      } catch (e) {
        this.errorMsg = 'Terjadi kesalahan jaringan.';
      } finally {
        this.moveSubmitting = false;
      }
    },

    async startClass() {
      this.starting = true;
      this.errorMsg = '';
      try {
        const res = await fetch('{{ route("trainer.attendance.start", $meeting) }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
          this.classState = 'in_progress';
          this.startedAt = data.started_at ?? '';
        } else {
          this.errorMsg = data.message ?? 'Gagal memulai kelas.';
        }
      } catch (e) {
        this.errorMsg = 'Terjadi kesalahan jaringan.';
      } finally {
        this.starting = false;
      }
    },

    async setStatus(student, status) {
      const prev = student.status;
      student.status = status;
      this.errorMsg = '';
      try {
        const res = await fetch('{{ route("trainer.attendance.status", $meeting) }}', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ student_id: student.id, status })
        });

        const data = await res.json();
        if (!data.success) {
          student.status = prev;
          this.errorMsg = data.message ?? 'Gagal update.';
        }
      } catch (e) {
        student.status = prev;
        this.errorMsg = 'Terjadi kesalahan jaringan.';
      }
    },

    async undoStatus(student) { await this.setStatus(student, 'pending'); },

    async endClass() {
      this.ending = true;
      this.errorMsg = '';
      try {
        const res = await fetch('{{ route("trainer.attendance.end", $meeting) }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
          this.classState = 'completed';
          this.confirmEnd = false;
        } else {
          this.errorMsg = data.message ?? 'Gagal mengakhiri kelas.';
          this.confirmEnd = false;
        }
      } catch (e) {
        this.errorMsg = 'Terjadi kesalahan jaringan.';
      } finally {
        this.ending = false;
      }
    },
  };
}
</script>
@endpush

