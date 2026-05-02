/**
 * Student Management - Inline Editing with Auto-Save
 * Features:
 * - Local search with debounce
 * - Inline editing with AJAX auto-save
 * - Dropdown filter integration
 * - Delete with confirmation
 * - Sticky header table with scroll
 */

// Debounce utility
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Toast notification
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

// Show inline loading indicator
function showInlineLoading(row, field) {
    const cell = row.querySelector(`[data-field="${field}"]`);
    if (cell) {
        cell.classList.add('relative');
        const existingLoader = cell.querySelector('.inline-loader');
        if (!existingLoader) {
            const loader = document.createElement('span');
            loader.className = 'inline-loader absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-gray-800/50';
            loader.innerHTML = '<svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            cell.appendChild(loader);
        }
    }
}

// Hide inline loading indicator
function hideInlineLoading(row, field) {
    const cell = row.querySelector(`[data-field="${field}"]`);
    if (cell) {
        const loader = cell.querySelector('.inline-loader');
        if (loader) loader.remove();
    }
}

// Inline update function - sends AJAX PATCH request
function inlineUpdate(studentId, field, value, row) {
    showInlineLoading(row, field);
    
    fetch(`/admin/students/${studentId}/inline`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ field, value })
    })
    .then(response => response.json())
    .then(data => {
        hideInlineLoading(row, field);
        if (data.success) {
            showToast(data.message || 'Saved automatically', 'success');
        } else {
            showToast(data.message || 'Update failed', 'error');
        }
    })
    .catch(error => {
        hideInlineLoading(row, field);
        console.error('Error:', error);
        showToast('Update failed', 'error');
    });
}

// Session time options (same as search dropdown)
const sessionTimeOptions = [
    '08:00 - 09:30',
    '09:30 - 11:00',
    '11:00 - 12:30',
    '13:00 - 14:30',
    '14:30 - 16:00',
    '16:00 - 17:30'
];

// Periode options (1x to 5x)
const periodeOptions = ['1x', '2x', '3x', '4x', '5x'];

// Create inline editable cell
function makeCellEditable(cell, studentId, field, currentValue) {
    // Prevent multiple edits at once
    if (cell.querySelector('input, select')) {
        return;
    }
    
    const originalValue = currentValue || '';
    const isSelect = field === 'trainer_id';
    const isPeriode = field === 'periode';
    const isSession = field === 'session_time';
    const trainers = window.studentTrainers || [];
    
    // Create input/select based on field type
    let input;
    if (isSelect) {
        // Trainer dropdown
        input = document.createElement('select');
        input.style.width = '100%';
        input.style.padding = '4px 8px';
        input.style.fontSize = '14px';
        input.style.border = '2px solid #3b82f6';
        input.style.borderRadius = '4px';
        input.style.backgroundColor = 'white';
        input.style.color = '#111827';
        input.setAttribute('data-field', field);
        input.setAttribute('data-student-id', studentId);
        
        // Add default option
        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.textContent = '- pilih trainer -';
        input.appendChild(defaultOpt);
        
        // Add trainer options
        trainers.forEach(trainer => {
            const opt = document.createElement('option');
            opt.value = trainer.id;
            opt.textContent = trainer.name;
            if (trainer.id == originalValue) opt.selected = true;
            input.appendChild(opt);
        });
    } else if (isPeriode) {
        // Periode dropdown (1x to 5x)
        input = document.createElement('select');
        input.style.width = '100%';
        input.style.padding = '4px 8px';
        input.style.fontSize = '14px';
        input.style.border = '2px solid #3b82f6';
        input.style.borderRadius = '4px';
        input.style.backgroundColor = 'white';
        input.style.color = '#111827';
        input.setAttribute('data-field', field);
        input.setAttribute('data-student-id', studentId);
        
        // Add default option
        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.textContent = '- pilih periode -';
        input.appendChild(defaultOpt);
        
        // Add periode options (1x to 5x)
        periodeOptions.forEach(periode => {
            const opt = document.createElement('option');
            opt.value = periode;
            opt.textContent = periode;
            if (periode === originalValue) opt.selected = true;
            input.appendChild(opt);
        });
    } else if (isSession) {
        // Session time dropdown
        input = document.createElement('select');
        input.style.width = '100%';
        input.style.padding = '4px 8px';
        input.style.fontSize = '14px';
        input.style.border = '2px solid #3b82f6';
        input.style.borderRadius = '4px';
        input.style.backgroundColor = 'white';
        input.style.color = '#111827';
        input.setAttribute('data-field', field);
        input.setAttribute('data-student-id', studentId);
        
        // Add default option
        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.textContent = '- pilih sesi -';
        input.appendChild(defaultOpt);
        
        // Add session time options
        sessionTimeOptions.forEach(session => {
            const opt = document.createElement('option');
            opt.value = session;
            opt.textContent = session;
            if (session === originalValue) opt.selected = true;
            input.appendChild(opt);
        });
    } else {
        input = document.createElement('input');
        input.type = 'text';
        input.value = originalValue;
        input.style.width = '100%';
        input.style.padding = '4px 8px';
        input.style.fontSize = '14px';
        input.style.border = '2px solid #3b82f6';
        input.style.borderRadius = '4px';
        input.style.backgroundColor = 'white';
        input.style.color = '#111827';
        input.setAttribute('data-field', field);
        input.setAttribute('data-student-id', studentId);
    }
    
// Clear and append
    cell.innerHTML = '';
    cell.appendChild(input);
    
    // Force focus and position cursor at start (left side) for text inputs
    input.focus();
    
    // For text inputs, set cursor at start (left side)
    if (input.tagName === 'INPUT') {
        input.setSelectionRange(0, 0);
    }
    
    // For select dropdowns, use change event
    if (input.tagName === 'SELECT') {
        // Handle click to stop cell click from re-triggering
        input.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        input.addEventListener('change', function(e) {
            e.stopPropagation();
            const newValue = this.value;
            // Only save if value actually changed (not empty when original was also empty)
            if (newValue !== originalValue) {
                inlineUpdate(studentId, field, newValue, cell.closest('tr'));
            }
            // Set cursor back to start for next edit
            renderCell(cell, field, newValue || originalValue, studentId);
        });
        
        // Handle blur to cancel - just restore original without saving if no change was made
        input.addEventListener('blur', function() {
            // Using setTimeout to ensure change event fires first
            setTimeout(() => {
                if (input.parentNode && input.value === originalValue) {
                    renderCell(cell, field, originalValue, studentId);
                }
            }, 100);
        });
    } else {
        // Handle blur (save on lose focus) for text inputs
        input.addEventListener('blur', function() {
            const newValue = this.value.trim();
            // Only save if value actually changed
            if (newValue !== originalValue) {
                inlineUpdate(studentId, field, newValue, cell.closest('tr'));
            }
            renderCell(cell, field, newValue || originalValue, studentId);
        });
        
        // Handle Enter key
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                this.blur();
            } else if (e.key === 'Escape') {
                renderCell(cell, field, originalValue, studentId);
            }
        });
    }
}

// Render cell in view mode
function renderCell(cell, field, value, studentId) {
    const isTrainer = field === 'trainer_id';
    const trainers = window.studentTrainers || [];
    
    if (isTrainer && value) {
        const trainer = trainers.find(t => t.id == value);
        value = trainer ? trainer.name : '-';
    } else if (!value) {
        value = '-';
    }
    
    cell.textContent = value;
    cell.setAttribute('data-field', field);
    cell.setAttribute('data-student-id', studentId);
    cell.classList.add('editable');
    cell.title = 'Klik untuk edit';
}

// Delete student with confirmation
function deleteStudent(studentId, studentName) {
    if (confirm(`Yakin ingin menghapus siswa "${studentName}"? Data tidak dapat dikembalikan.`)) {
        fetch(`/admin/students/${studentId}/delete`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove row from DOM
                const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
                if (row) {
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Delete failed', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Delete failed', 'error');
        });
    }
}

// Initialize inline editing
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('student-table-body');
    const searchInput = document.getElementById('search-local');
    
    // Local search with debounce
    if (searchInput && tableBody) {
        searchInput.addEventListener('input', debounce(function(e) {
            const query = e.target.value.toLowerCase();
            const rows = tableBody.querySelectorAll('tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        }, 300));
    }
    
    // Make cells editable on click
    document.querySelectorAll('.editable').forEach(cell => {
        cell.addEventListener('click', function() {
            const field = this.getAttribute('data-field');
            const studentId = this.getAttribute('data-student-id');
            if (field && studentId) {
                const currentValue = this.textContent === '-' ? '' : this.textContent;
                makeCellEditable(this, studentId, field, currentValue);
            }
        });
    });
    
    // Trainer filter - local filter
    const trainerFilter = document.getElementById('filter-trainer');
    if (trainerFilter && tableBody) {
        trainerFilter.addEventListener('change', function() {
            const selectedTrainerId = this.value;
            const rows = tableBody.querySelectorAll('tr');
            const trainers = window.studentTrainers || [];
            
            rows.forEach(row => {
                const trainerCell = row.querySelector('[data-field="trainer_id"]');
                if (!trainerCell) return;
                
                if (!selectedTrainerId) {
                    row.style.display = '';
                } else {
                    // Get trainer name from the cell text and compare
                    const trainerName = trainerCell.textContent.trim();
                    const trainer = trainers.find(t => t.id == selectedTrainerId);
                    const matches = trainer && trainer.name === trainerName;
                    row.style.display = matches ? '' : 'none';
                }
            });
        });
    }
    
    // Session filter - local filter
    const sessionFilter = document.getElementById('filter-session');
    if (sessionFilter && tableBody) {
        sessionFilter.addEventListener('change', function() {
            const selectedSession = this.value;
            const rows = tableBody.querySelectorAll('tr');
            
            rows.forEach(row => {
                const sessionCell = row.querySelector('[data-field="session_time"]');
                if (!sessionCell) return;
                
                if (!selectedSession) {
                    row.style.display = '';
                } else {
                    const sessionValue = sessionCell.textContent.trim();
                    const matches = sessionValue === selectedSession;
                    row.style.display = matches ? '' : 'none';
                }
            });
        });
    }
    
});
