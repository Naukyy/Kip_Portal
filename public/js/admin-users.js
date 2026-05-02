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

    // Render user row
    function renderUserRow(user) {
        return `<tr>
            <td class="border px-2 py-1">${user.employee_code || '-'}</td>
            <td class="border px-2 py-1">${user.name}<br><span class="text-xs text-gray-500">${user.nickname || ''}</span></td>
            <td class="border px-2 py-1">${user.role}</td>
            <td class="border px-2 py-1">${user.whatsapp || '-'}</td>
            <td class="border px-2 py-1">${user.email}</td>
            <td class="border px-2 py-1">${user.is_active ? 'Aktif' : 'Nonaktif'}</td>
            <td class="border px-2 py-1">
                <button class="edit-user px-2 py-1 bg-yellow-500 text-white rounded mr-1" data-id="${user.id}">Edit</button>
                <button class="delete-user px-2 py-1 bg-red-600 text-white rounded" data-id="${user.id}">Hapus</button>
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
                if (confirm('Yakin hapus user ini?')) {
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
        document.getElementById('modal-title').textContent = user ? 'Edit User' : 'Tambah User';
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
        
        // Build data object from form fields (more reliable than FormData)
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
        
        // Debug: log what we're sending
        console.log('Saving user:', { url, method, id, data });
        
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
                        console.error('Error response:', err);
                        throw new Error(err.message || 'Validation failed');
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
                showToast(err.message || 'Terjadi kesalahan', false);
            });
    }

    // Toast notification
    function showToast(message, success = true) {
        toast.textContent = message;
        toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded shadow-lg z-50 ${success ? 'bg-green-600' : 'bg-red-600'}`;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 2500);
    }

    // Get CSRF token
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }
});
