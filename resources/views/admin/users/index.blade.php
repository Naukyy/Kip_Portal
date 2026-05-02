@extends('layouts.app')

@section('title', 'Akun Pegawai')

@section('content')
<div class="p-6 max-w-6xl mx-auto w-full">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-2">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Akun Pegawai</h1>
        <button id="btn-add-user" class="px-4 py-2 bg-blue-600 text-white rounded neon-border button-glow">Tambah User</button>
    </div>
    <div class="mb-4">
        <input type="text" id="search-user" class="w-full px-3 py-2 border rounded neon-border bg-white dark:bg-gray-900 text-gray-900 dark:text-white" placeholder="Cari user (nama, kode, nickname)...">
    </div>
    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama & Nickname</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">WhatsApp</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody id="user-table-body">
                <!-- Data user akan dimuat via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add/Edit User -->
<div id="admin-user-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg w-full max-w-lg p-6 relative">
        <button id="close-user-modal" class="absolute top-2 right-2 text-gray-500 hover:text-red-500">&times;</button>
        <h2 id="modal-title" class="text-xl font-bold mb-4">Tambah User</h2>
        <form id="user-form">
            <input type="hidden" name="id" id="user-id">
            <div class="mb-3">
                <label class="block mb-1">Kode</label>
                <input type="text" name="employee_code" id="user-code" class="w-full px-3 py-2 border rounded neon-border bg-white dark:bg-gray-900 text-gray-900 dark:text-white" required>
            </div>
            <div class="mb-3">
                <label class="block mb-1">Nama</label>
                <input type="text" name="name" id="user-name" class="w-full px-3 py-2 border rounded neon-border bg-white dark:bg-gray-900 text-gray-900 dark:text-white" required>
            </div>
            <div class="mb-3">
                <label class="block mb-1">Nickname</label>
                <input type="text" name="nickname" id="user-nickname" class="w-full px-3 py-2 border rounded neon-border bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
            </div>
            <div class="mb-3">
                <label class="block mb-1">Email</label>
                <input type="email" name="email" id="user-email" class="w-full px-3 py-2 border rounded neon-border bg-white dark:bg-gray-900 text-gray-900 dark:text-white" required>
            </div>
            <div class="mb-3">
                <label class="block mb-1">WhatsApp</label>
                <input type="text" name="whatsapp" id="user-wa" class="w-full px-3 py-2 border rounded neon-border bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
            </div>
<div class="mb-3">
                <label class="block mb-1">Role</label>
                <select name="role" id="user-role" class="w-full px-3 py-2 border rounded neon-border bg-white dark:bg-gray-900 text-gray-900 dark:text-white" required>
                    <option value="Trainer Junior">Trainer Junior</option>
                    <option value="Trainer Senior">Trainer Senior</option>
                    <option value="Management">Management</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>
            <div class="mb-3 flex items-center">
                <input type="checkbox" name="is_active" id="user-active" value="1" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                <label for="user-active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Aktif</label>
            </div>
            <div class="mb-3">
                <label class="block mb-1">Password</label>
                <input type="password" name="password" id="user-password" class="w-full px-3 py-2 border rounded neon-border bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                <small class="text-gray-500">Kosongkan jika tidak ingin mengubah password.</small>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded neon-border button-glow">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-4 right-4 bg-gray-900 text-white px-4 py-2 rounded shadow-lg hidden z-50"></div>
@endsection

@push('scripts')
<script src="/js/admin-users.js"></script>
@endpush
