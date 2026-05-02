@extends('layouts.app')

@section('title', 'Akun Pegawai')

@section('content')
<div class="p-6 md:p-8 max-w-7xl mx-auto w-full transition-colors duration-300">
    
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white tracking-tight">User Accounts</h1>
            <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mt-1">Generate and manage system access for all staff.</p>
        </div>
        
        <button id="btn-add-user" class="flex items-center justify-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-semibold shadow-[0_0_15px_rgba(37,99,235,0.4)] transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create User
        </button>
    </div>

    <!-- Search Bar -->
    <div class="relative mb-6">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </span>
        <input type="text" id="search-user" 
            class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-[#111827] border border-gray-200 dark:border-gray-800 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-gray-900 dark:text-white transition-all shadow-sm" 
            placeholder="Search users by name, code, or nickname...">
    </div>

    <!-- Table Container -->
    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#111827] shadow-lg">
        <table class="min-w-full text-sm text-left">
            <thead class="text-xs text-gray-500 dark:text-gray-400 uppercase bg-gray-50 dark:bg-[#1f2937]/50 border-b border-gray-200 dark:border-gray-800">
                <tr>
                    <th class="px-6 py-4 font-semibold tracking-wider">Code</th>
                    <th class="px-6 py-4 font-semibold tracking-wider">Name & Nickname</th>
                    <th class="px-6 py-4 font-semibold tracking-wider">Role</th>
                    <th class="px-6 py-4 font-semibold tracking-wider">WhatsApp</th>
                    <th class="px-6 py-4 font-semibold tracking-wider">Email</th>
                    <th class="px-6 py-4 font-semibold tracking-wider">Status</th>
                    <th class="px-6 py-4 font-semibold tracking-wider text-center">Actions</th>
                </tr>
            </thead>
            <!-- ID ini SANGAT PENTING untuk JS Anda -->
            <tbody id="user-table-body" class="divide-y divide-gray-100 dark:divide-gray-800/60">
                <!-- Data user akan dimuat via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add/Edit User (Fungsi & ID asli dipertahankan, UI dipercantik) -->
<div id="admin-user-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center hidden z-50">
    <div class="bg-white dark:bg-[#111827] rounded-xl shadow-2xl w-full max-w-lg p-6 relative border border-gray-200 dark:border-gray-800 mx-4">
        <button id="close-user-modal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 transition-colors">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
        <h2 id="modal-title" class="text-xl font-bold mb-5 text-gray-900 dark:text-white">Tambah User</h2>
        
        <form id="user-form" class="space-y-4">
            <input type="hidden" name="id" id="user-id">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode</label>
                    <input type="text" name="employee_code" id="user-code" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nickname</label>
                    <input type="text" name="nickname" id="user-nickname" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama</label>
                <input type="text" name="name" id="user-name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input type="email" name="email" id="user-email" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">WhatsApp</label>
                <input type="text" name="whatsapp" id="user-wa" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                <select name="role" id="user-role" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none" required>
                    <option value="Trainer Junior">Trainer Junior</option>
                    <option value="Trainer Senior">Trainer Senior</option>
                    <option value="Management">Management</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                <input type="password" name="password" id="user-password" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 outline-none">
                <small class="text-gray-500 dark:text-gray-400">Kosongkan jika tidak ingin mengubah password.</small>
            </div>

            <div class="flex items-center pt-2">
                <input type="checkbox" name="is_active" id="user-active" value="1" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 bg-white dark:bg-gray-800" checked>
                <label for="user-active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</label>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-2.5 bg-green-600 hover:bg-green-500 text-white font-medium rounded-lg shadow-lg shadow-green-500/30 transition-all">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-4 right-4 bg-gray-900 dark:bg-gray-800 text-white px-6 py-3 rounded-lg shadow-xl hidden z-50 border border-gray-700"></div>
@endsection

@push('scripts')
<script src="/js/admin-users.js"></script>
@endpush