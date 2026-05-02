# User Management Implementation Tasks

## Progress

# Admin Features Breakdown

## 1. User Management
- [ ] CRUD User (Trainer/Management/Admin)
	- [ ] Tambah, edit, hapus, lihat user
	- [ ] Field: kode, nama, nickname, email, password, role, WhatsApp

## 2. All Students
- [ ] CRUD Siswa (inline edit)
	- [ ] Lihat daftar siswa
	- [ ] Filter berdasarkan trainer & sesi
	- [ ] Tambah, edit (inline), hapus siswa

## 3. Attendance Recap
- [ ] Rekap Absensi Bulanan
	- [ ] Filter tahun, bulan, trainer, sesi
	- [ ] Tampilkan status absensi per hari (A, P, Ab, SUB)
	- [ ] Ekspor data ke CSV

## 4. Trainer Payroll
- [ ] Ledger Gaji Trainer Harian
	- [ ] Filter tahun, bulan, trainer
	- [ ] Tampilkan daily rate (berdasarkan jumlah siswa hadir)
	- [ ] Hitung total gaji per trainer
	- [ ] Ubah posisi trainer (Junior/Senior) → hitung ulang otomatis
	- [ ] Ekspor data ke CSV

## 5. Data Settings
- [ ] Master Data Payroll
	- [ ] Salary Range: per posisi (Junior/Senior) dan tier jumlah siswa (1-5, 6-7, 8-10, 11+)
	- [ ] Additional Incentives: kategori & nominal
	- [ ] Deductions: kategori & nominal

## 6. Salary Adjustments
- [ ] Final Salary & Transaksi
	- [ ] Tampilkan final salary per trainer (base + incentives - deductions)
	- [ ] Modal daftar transaksi & tambah transaksi baru (tanggal, kategori, jumlah)
	- [ ] Status salary: Draft/Paid

## Implementation Details

### Step 2: Update Routes (web.php)
Add AJAX routes using AdminUserController:
- GET /admin/users - List users with search
- GET /admin/users/search - AJAX search
- GET /admin/users/{user} - Get user data for edit
- POST /admin/users - Create new user
- PUT /admin/users/{user} - Update user
- DELETE /admin/users/{user} - Delete user
- POST /admin/users/{user}/reset-password - Reset password
- POST /admin/users/{user}/toggle-status - Toggle active status

### Step 3: Create Blade Template
Create resources/views/admin/users/index.blade.php with:
- User table with CODE, NAME & NICKNAME, ROLE, CONTACT (WA), CREDENTIALS columns
- Search bar with AJAX search
- Add/Edit Modal (#admin-user-modal)
- Tailwind CSS styling (neon-border, button-glow effects)

### Step 4: JavaScript Integration
Implement Fetch API replacing google.script.run:
- AJAX load users on page load
- AJAX search functionality
- AJAX save user (create/update)
- Toast notifications for success/error
