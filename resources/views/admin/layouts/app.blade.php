<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">
    @stack('head')
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-gray-800 text-white px-4 py-3 mb-6">
        <div class="container mx-auto flex justify-between items-center">
            <div class="font-bold text-lg">KIP Portal Admin</div>
            <div>
                <a href="/admin/users" class="mr-4 hover:underline">Users</a>
                <a href="/admin/students" class="mr-4 hover:underline">Siswa</a>
                <a href="/admin/recap" class="mr-4 hover:underline">Rekap</a>
                <a href="/admin/payroll" class="mr-4 hover:underline">Payroll</a>
                <a href="/admin/settings" class="hover:underline">Settings</a>
            </div>
        </div>
    </nav>
    <main>
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
