<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: false, darkMode: localStorage.getItem('darkMode') === 'true' }"
      :class="{ 'dark': darkMode }" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'KIP Portal') }} — @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full bg-gray-50 dark:bg-gray-950 font-sans antialiased">

<div class="flex h-full">
    {{-- Sidebar --}}
    <aside class="hidden lg:flex lg:flex-col w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 flex-shrink-0">
        @include('layouts.partials.sidebar')
    </aside>

    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarOpen" x-transition.opacity
         class="fixed inset-0 bg-black/40 z-40 lg:hidden"
         @click="sidebarOpen = false"></div>

    <aside x-show="sidebarOpen"
           class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 z-50 lg:hidden"
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0">
        @include('layouts.partials.sidebar')
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col min-h-0 overflow-auto">
        {{-- Top navbar --}}
        <header class="flex-shrink-0 h-16 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 flex items-center px-4 lg:px-6 gap-4">
            <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white flex-1">@yield('title', 'Dashboard')</h1>
            
            {{-- Dark mode toggle --}}
            <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                    class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>

            {{-- User menu --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center gap-2 text-sm">
                    <div class="w-8 h-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-medium text-xs">
                        {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                    <span class="hidden sm:block text-gray-700 dark:text-gray-300">{{ auth()->user()->nickname ?? auth()->user()->name }}</span>
                </button>
                <div x-show="open" @click.outside="open = false"
                     class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
                    <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-800">
                        <p class="text-xs text-gray-500">{{ auth()->user()->role }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-950">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mx-6 mt-4 p-4 bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-300 text-sm">
            {{ session('success') }}
        </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 p-4 lg:p-6">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>