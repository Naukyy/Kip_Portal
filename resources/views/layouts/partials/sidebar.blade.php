<div class="flex flex-col h-full">
    {{-- Logo --}}
    <div class="flex items-center gap-3 px-6 h-16 border-b border-gray-200 dark:border-gray-800">
        <div class="w-8 h-8 rounded-lg bg-primary-600 flex items-center justify-center">
            <span class="text-white font-bold text-sm">K</span>
        </div>
        <div>
            <p class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">KIP Portal</p>
            <p class="text-xs text-gray-500">Kampung Inggris Pontianak</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        @if(auth()->user()->isAdmin())
            {{-- ADMIN MENU --}}
            <x-nav-item route="admin.dashboard" icon="home">Dashboard</x-nav-item>
            <x-nav-item route="admin.users.index" icon="users">Akun Pegawai</x-nav-item>
            <x-nav-item route="admin.students.index" icon="academic-cap">Data Siswa</x-nav-item>
            <x-nav-item route="admin.recap.index" icon="table">Rekap Global</x-nav-item>
            <x-nav-item route="admin.payroll.index" icon="currency-dollar">Payroll Trainer</x-nav-item>
            <x-nav-item route="admin.adjustments.index" icon="adjustments">Penyesuaian Gaji</x-nav-item>
            <x-nav-item route="admin.settings.index" icon="cog">Pengaturan</x-nav-item>
        @else
            {{-- TRAINER MENU --}}
            <x-nav-item route="trainer.dashboard" icon="home">Dashboard</x-nav-item>
            <x-nav-item route="trainer.students.index" icon="users">Murid Saya</x-nav-item>
            <x-nav-item route="trainer.attendance.index" icon="clipboard-list">Presensi Harian</x-nav-item>
            <x-nav-item route="trainer.attendance.recap" icon="table">Rekap Bulanan</x-nav-item>
            <x-nav-item route="trainer.payslip.index" icon="document-text">Slip Gaji</x-nav-item>
        @endif
    </nav>

    {{-- User info bottom --}}
    <div class="px-3 py-4 border-t border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-3 px-3 py-2">
            <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-700 dark:text-primary-300 font-medium text-xs">
                {{ substr(auth()->user()->name, 0, 2) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->role }}</p>
            </div>
        </div>
    </div>
</div>