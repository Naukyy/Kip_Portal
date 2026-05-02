// admin-users.js

document.addEventListener('DOMContentLoaded', function () {
    const userTableBody = document.getElementById('user-table-body');
    const searchInput = document.getElementById('search-user');
    const addUserBtn = document.getElementById('btn-add-user');
    const userModal = document.getElementById('admin-user-modal');
    const closeModalBtn = document.getElementById('close-user-modal');
    const userForm = document.getElementById('user-form');
    const toast = document.getElementById('toast');

    // Load users on page load
    loadUsers();

    // Search functionality
    searchInput.addEventListener('input', function () {
        loadUsers(this.value);
    });

    // Show modal for add
    addUserBtn.addEventListener('click', function () {
        openUserModal();
    });

    // Close modal
    closeModalBtn.addEventListener('click', function () {
        userModal.classList.add('hidden');
    });

    // Save user (create/update)
    userForm.addEventListener('submit', function (e) {
        e.preventDefault();
        saveUser();
    });

    // Load users
    function loadUsers(search = '') {
        fetch(`/admin/users/search?q=${encodeURIComponent(search)}`)
            .then(res => res.json())
            .then(data => {
                userTableBody.innerHTML = '';
                data.users.forEach(user => {
                    userTableBody.innerHTML += renderUserRow(user);
                });
                attachRowEvents();
            });
    }

    // Render user row (Desain Ulang: Aman, Kontras Tinggi, dan Ghost Buttons)
    function renderUserRow(user) {
        // Badge Status yang lebih elegan dan aman untuk semua versi Tailwind
        const isActiveBadge = user.is_active 
            ? `<span class="px-2 py-1 text-xs font-semibold rounded-md bg-green-50 text-green-600 border border-green-200 dark:bg-gray-800 dark:text-green-400 dark:border-gray-700">Aktif</span>` 
            : `<span class="px-2 py-1 text-xs font-semibold rounded-md bg-red-50 text-red-600 border border-red-200 dark:bg-gray-800 dark:text-red-400 dark:border-gray-700">Nonaktif</span>`;

        return `
        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200 group bg-transparent">
            <!-- Kode -->
            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600 dark:text-blue-400">
                ${user.employee_code || '-'}
            </td>
            
            <!-- Nama & Nickname -->
            <td class="px-6 py-4">
                <div class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-white transition-colors">${user.name}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">AKA: ${user.nickname || '-'}</div>
            </td>
            
            <!-- Role -->
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-50 text-blue-600 border border-blue-200 dark:bg-gray-800 dark:text-blue-400 dark:border-gray-700">
                    ${user.role}
                </span>
            </td>
            
            <!-- WhatsApp -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                ${user.whatsapp || '-'}
            </td>
            
            <!-- Email -->
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="text-gray-900 dark:text-gray-200">${user.email}</div>
                <div class="text-xs text-gray-400 dark:text-gray-500 mt-1 font-mono">PW: ••••••••</div>
            </td>
            
            <!-- Status -->
            <td class="px-6 py-4 whitespace-nowrap">
                ${isActiveBadge}
            </td>
            
            <!-- Aksi: Ghost Buttons (Menyatu dengan tema, muncul efek saat di-hover) -->
            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                <div class="flex items-center justify-center gap-1 opacity-70 group-hover:opacity-100 transition-opacity duration-200">
                    <!-- Edit Button -->
                    <button class="edit-user p-2 rounded-lg text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-700 transition-all focus:outline-none" data-id="${user.id}" title="Edit Data">
                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </button>
                    <!-- Delete Button -->
                    <button class="delete-user p-2 rounded-lg text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-gray-700 transition-all focus:outline-none" data-id="${user.id}" title="Hapus Data">
                        <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </td>
        </tr>`;
    }

    // Attach events to edit/delete buttons
    function attachRowEvents() {
        document.querySelectorAll('.edit-user').forEach(btn => {
            btn.onclick = function () {
                fetch(`/admin/users/${btn.dataset.id}`)
                    .then(res => res.json())
                    .then(user => openUserModal(user));
            };
        });
        document.querySelectorAll('.delete-user').forEach(btn => {
            btn.onclick = function () {
                if (confirm('Yakin ingin menghapus user ini secara permanen?')) {
                    fetch(`/admin/users/${btn.dataset.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': getCsrfToken() }
                    })
                        .then(res => res.json())
                        .then(res => {
                            showToast(res.message, res.success);
                            loadUsers(searchInput.value);
                        });
                }
            };
        });
    }

    // Open modal (add/edit)
    function openUserModal(user = null) {
        userModal.classList.remove('hidden');
        userForm.reset();
        document.getElementById('user-id').value = ''; // Always reset ID
        document.getElementById('modal-title').textContent = user ? 'Edit Akun Pegawai' : 'Tambah Akun Pegawai';
        if (user) {
            document.getElementById('user-id').value = user.id;
            document.getElementById('user-code').value = user.employee_code || '';
            document.getElementById('user-name').value = user.name || '';
            document.getElementById('user-nickname').value = user.nickname || '';
            document.getElementById('user-email').value = user.email || '';
            document.getElementById('user-wa').value = user.whatsapp || '';
            document.getElementById('user-role').value = user.role || 'Trainer Junior';
            // Handle is_active checkbox
            document.getElementById('user-active').checked = user.is_active === true || user.is_active === 1 || user.is_active === '1';
        }
    }

    // Save user (create/update)
    function saveUser() {
        const id = document.getElementById('user-id').value;
        const url = id ? `/admin/users/${id}` : '/admin/users';
        const method = id ? 'PUT' : 'POST';
        
        // Build data object from form fields
        const data = {
            employee_code: document.getElementById('user-code').value,
            name: document.getElementById('user-name').value,
            nickname: document.getElementById('user-nickname').value,
            email: document.getElementById('user-email').value,
            whatsapp: document.getElementById('user-wa').value,
            role: document.getElementById('user-role').value,
            is_active: document.getElementById('user-active').checked ? 1 : 0,
            password: document.getElementById('user-password').value
        };
        
        fetch(url, {
            method: method,
            headers: { 
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => {
                        throw new Error(err.message || 'Validasi gagal, periksa kembali input Anda.');
                    });
                }
                return res.json();
            })
            .then(res => {
                showToast(res.message, res.success);
                if (res.success) {
                    userModal.classList.add('hidden');
                    loadUsers(searchInput.value);
                }
            })
            .catch(err => {
                console.error('Save error:', err);
                showToast(err.message || 'Terjadi kesalahan sistem', false);
            });
    }

    // Toast notification
    function showToast(message, success = true) {
        toast.textContent = message;
        toast.className = `fixed bottom-6 right-6 px-6 py-3 rounded-lg shadow-2xl text-white font-medium z-50 transition-all duration-300 transform translate-y-0 ${success ? 'bg-green-600 shadow-green-500/30' : 'bg-red-600 shadow-red-500/30'}`;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3000);
    }

    // Get CSRF token
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }
});