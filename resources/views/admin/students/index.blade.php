@extends('layouts.app')

@section('title', 'Data Siswa - Inline Edit Mode')

@section('content')
<div class="p-6 md:p-8 max-w-7xl mx-auto w-full transition-colors duration-300">
    
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white tracking-tight">Data Siswa</h1>
            <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Kelola data siswa Kampung Inggris.</p>
        </div>
        
        <button id="btn-add-student" class="flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-semibold shadow-[0_0_15px_rgba(37,99,235,0.4)] transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Siswa
        </button>
    </div>

<!-- Local Search with Debounce -->
    <div class="relative mb-4">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </span>
        <input type="text" id="search-local" 
            class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-[#111827] border border-gray-200 dark:border-gray-800 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-gray-900 dark:text-white transition-all shadow-sm" 
            placeholder="Search name/ID (local filter)...">
    </div>

    <!-- Filters: Trainer & Session -->
    <div class="flex flex-wrap gap-3 mb-4">
        <select id="filter-trainer" class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="">Semua Trainer</option>
            @foreach($trainers as $trainer)
                <option value="{{ $trainer->id }}">{{ $trainer->name }}</option>
            @endforeach
        </select>
        <select id="filter-session" class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="">Semua Sesi</option>
            <option value="08:00 - 09:30">08:00 - 09:30</option>
            <option value="09:30 - 11:00">09:30 - 11:00</option>
            <option value="11:00 - 12:30">11:00 - 12:30</option>
            <option value="13:00 - 14:30">13:00 - 14:30</option>
            <option value="14:30 - 16:00">14:30 - 16:00</option>
            <option value="16:00 - 17:30">16:00 - 17:30</option>
        </select>
    </div>

    <!-- Table Container with Sticky Header & Custom Scrollbar -->
    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#111827] shadow-lg max-h-[600px] overflow-y-auto" id="table-container">
        <table class="min-w-full text-sm text-left">
            <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-[#1f2937]/50 border-b border-gray-200 dark:border-gray-800 sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-3 font-semibold tracking-wider">ID</th>
                    <th class="px-4 py-3 font-semibold tracking-wider">Name</th>
                    <th class="px-4 py-3 font-semibold tracking-wider">Periode</th>
                    <th class="px-4 py-3 font-semibold tracking-wider">Sessions</th>
                    <th class="px-4 py-3 font-semibold tracking-wider">Schedule</th>
                    <th class="px-4 py-3 font-semibold tracking-wider">WA</th>
                    <th class="px-4 py-3 font-semibold tracking-wider">Email</th>
                    <th class="px-4 py-3 font-semibold tracking-wider">Trainer</th>
                    <th class="px-4 py-3 font-semibold tracking-wider text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="student-table-body" class="divide-y divide-gray-100 dark:divide-gray-800/60">
                @forelse($students as $student)
                    <tr data-student-id="{{ $student->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
<!-- ID - Inline Editable -->
                        <td class="px-4 py-3 editable cursor-pointer" data-field="student_id" data-student-id="{{ $student->id }}">
                            {{ $student->student_id }}
                        </td>
                        <!-- NAME - Inline Editable -->
                        <td class="px-4 py-3 editable cursor-pointer" data-field="name" data-student-id="{{ $student->id }}">
                            {{ $student->name }}
                        </td>
                        <!-- PERIODE - Inline Editable -->
                        <td class="px-4 py-3 editable cursor-pointer" data-field="periode" data-student-id="{{ $student->id }}">
                            {{ $student->periode ?? '-' }}
                        </td>
                        <!-- SESSIONS - Inline Editable -->
                        <td class="px-4 py-3 editable cursor-pointer" data-field="session_time" data-student-id="{{ $student->id }}">
                            {{ $student->session_time ?? '-' }}
                        </td>
                        <!-- SCHEDULE - Inline Editable -->
                        <td class="px-4 py-3 editable cursor-pointer" data-field="schedule" data-student-id="{{ $student->id }}">
                            {{ $student->schedule ?? '-' }}
                        </td>
                        <!-- WA - Inline Editable -->
                        <td class="px-4 py-3 editable cursor-pointer" data-field="phone" data-student-id="{{ $student->id }}">
                            {{ $student->phone ?? '-' }}
                        </td>
                        <!-- EMAIL - Inline Editable -->
                        <td class="px-4 py-3 editable cursor-pointer" data-field="email" data-student-id="{{ $student->id }}">
                            {{ $student->email ?? '-' }}
                        </td>
                        <!-- TRAINER - Dropdown Inline Edit -->
                        <td class="px-4 py-3 editable cursor-pointer" data-field="trainer_id" data-student-id="{{ $student->id }}">
                            {{ $student->trainer->name ?? '-' }}
                        </td>
                        <!-- Actions -->
                        <td class="px-4 py-3 text-center">
                            <button type="button" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" onclick="deleteStudent({{ $student->id }}, '{{ $student->name }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            Tidak ada data siswa.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $students->links() }}
    </div>
</div>

<!-- Modal Add/Edit Student -->
<div id="student-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center hidden z-50">
    <div class="bg-white dark:bg-[#111827] rounded-xl shadow-2xl w-full max-w-lg p-6 relative border border-gray-200 dark:border-gray-800 mx-4">
        <button id="close-student-modal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
        <h2 id="modal-title" class="text-xl font-bold mb-5 text-gray-900 dark:text-white">Tambah Siswa</h2>
        
<form id="student-form" method="POST" action="{{ route('admin.students.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ID Siswa</label>
                <input type="text" name="student_id" id="student-id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="STD-001">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Siswa *</label>
                <input type="text" name="name" id="student-name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trainer *</label>
                <select name="trainer_id" id="student-trainer" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                    <option value="">Pilih Trainer</option>
                    @foreach($trainers as $trainer)
                        <option value="{{ $trainer->id }}">{{ $trainer->name }}</option>
                    @endforeach
                </select>
            </div>

<div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Periode</label>
                <select name="periode" id="student-periode" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Pilih Periode</option>
                    <option value="1x">1x</option>
                    <option value="2x">2x</option>
                    <option value="3x">3x</option>
                    <option value="4x">4x</option>
                    <option value="5x">5x</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sessions</label>
                <select name="session_time" id="student-session" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Pilih Sesi</option>
                    <option value="08:00 - 09:30">08:00 - 09:30</option>
                    <option value="09:30 - 11:00">09:30 - 11:00</option>
                    <option value="11:00 - 12:30">11:00 - 12:30</option>
                    <option value="13:00 - 14:30">13:00 - 14:30</option>
                    <option value="14:30 - 16:00">14:30 - 16:00</option>
                    <option value="16:00 - 17:30">16:00 - 17:30</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Schedule</label>
                <input type="text" name="schedule" id="student-schedule" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Senin, Kamis">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telepon</label>
                <input type="text" name="phone" id="student-phone" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input type="email" name="email" id="student-email" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Orang Tua</label>
                <input type="text" name="parent_name" id="student-parent-name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telepon Orang Tua</label>
                <input type="text" name="parent_phone" id="student-parent-phone" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <div class="md:col-span-2 flex items-center pt-2">
                <input type="checkbox" name="is_active" id="student-active" value="1" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-800" checked>
                <label for="student-active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</label>
            </div>

            <div class="md:col-span-2 flex justify-end pt-4">
                <button type="submit" class="px-6 py-2.5 bg-green-600 hover:bg-green-500 text-white font-medium rounded-lg shadow-lg shadow-green-500/30 transition-all">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-4 right-4 bg-gray-900 dark:bg-gray-800 text-white px-6 py-3 rounded-lg shadow-xl hidden z-50 border border-gray-700"></div>

@endsection

@push('scripts')
<script src="/js/admin-students.js"></script>
<script>
// Make trainers available globally for inline editing
window.studentTrainers = @json($trainers->map(fn($t) => ['id' => $t->id, 'name' => $t->name]));

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('student-modal');
    const btnAdd = document.getElementById('btn-add-student');
    const btnClose = document.getElementById('close-student-modal');
    const form = document.getElementById('student-form');
    const modalTitle = document.getElementById('modal-title');
    
    // Open modal for add
    btnAdd.addEventListener('click', function() {
        modalTitle.textContent = 'Tambah Siswa';
        form.action = '{{ route("admin.students.store") }}';
        form.method = 'POST';
        form.reset();
        modal.classList.remove('hidden');
    });
    
    // Close modal
    btnClose.addEventListener('click', function() {
        modal.classList.add('hidden');
    });
    
    // Close modal on outside click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
    
    // Show toast if success message exists
    @if(session('success'))
        showToast('{{ session("success") }}', 'success');
    @endif
});

// Edit student function (for modal-based editing)
function editStudent(id, studentId, name, periode, sessionTime, schedule, phone, email, trainerId, parentName, parentPhone, isActive) {
    const modal = document.getElementById('student-modal');
    const form = document.getElementById('student-form');
    const modalTitle = document.getElementById('modal-title');
    
    modalTitle.textContent = 'Edit Siswa';
    form.action = '/admin/students/' + id;
    form.method = 'POST';
    
    // Add hidden method field for PUT
    let methodInput = form.querySelector('input[name="_method"]');
    if (!methodInput) {
        methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        form.appendChild(methodInput);
    }
    methodInput.value = 'PUT';
    
    // Fill form fields
    document.getElementById('student-id').value = studentId || '';
    document.getElementById('student-name').value = name || '';
    document.getElementById('student-trainer').value = trainerId || '';
    document.getElementById('student-periode').value = periode || '';
    document.getElementById('student-session').value = sessionTime || '';
    document.getElementById('student-schedule').value = schedule || '';
    document.getElementById('student-phone').value = phone || '';
    document.getElementById('student-email').value = email || '';
    document.getElementById('student-parent-name').value = parentName || '';
    document.getElementById('student-parent-phone').value = parentPhone || '';
    document.getElementById('student-active').checked = isActive;
    
    modal.classList.remove('hidden');
}

// Toast is now in admin-students.js, but keep this wrapper for compatibility
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    
    toast.textContent = message;
    toast.className = `fixed bottom-4 right-4 text-white px-6 py-3 rounded-lg shadow-xl z-50 border ${
        type === 'success' ? 'bg-green-600 border-green-500' : 
        type === 'error' ? 'bg-red-600 border-red-500' : 'bg-gray-900 border-gray-700'
    }`;
    toast.classList.remove('hidden');
    
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 3000);
}
</script>
@endpush
