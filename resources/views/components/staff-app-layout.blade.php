<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Staff Portal</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex bg-[#f3f3f3]" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
            
            <div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden" aria-hidden="true"></div>
            <aside id="staff-sidebar" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] bg-[#590004] shadow-2xl flex flex-col justify-between transform transition-transform duration-200 lg:static lg:max-w-none lg:translate-x-0">
                <div>
                    <div class="h-20 flex items-center px-8 border-b border-[#250001]">
                        <img src="{{ asset('LOGO/OSDW.png') }}" alt="OSDW Logo" class="h-12 w-12 object-contain">
                        <div class="ml-3">
                            <span class="block text-[#f3f3f3] font-bold text-lg tracking-wide">Staff Portal</span>
                            <span class="block text-xs text-gray-300">CSU Disciplinary System</span>
                        </div>
                    </div>
                    
                    <nav @click="if ($event.target.closest('a')) sidebarOpen = false" class="mt-8 px-4 space-y-2 overflow-y-auto">
                        <a href="{{ route('staff.dashboard') }}" 
                           class="flex items-center px-4 py-3 {{ request()->routeIs('staff.dashboard') ? 'bg-[#250001] text-[#f3f3f3] shadow-inner border-l-4 border-[#a50104]' : 'text-gray-300 hover:bg-[#250001] hover:text-[#f3f3f3]' }} rounded-xl font-medium transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                            Main Incident Report Hub
                        </a>
                        
                        <a href="{{ route('staff.my-reports') }}" 
                           class="flex items-center px-4 py-3 {{ request()->routeIs('staff.my-reports') ? 'bg-[#250001] text-[#f3f3f3] shadow-inner border-l-4 border-[#a50104]' : 'text-gray-300 hover:bg-[#250001] hover:text-[#f3f3f3]' }} rounded-xl font-medium transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                           Submitted Reports
                        </a>

                        <a href="{{ route('staff.students') }}"
                           class="flex items-center px-4 py-3 {{ request()->routeIs('staff.students') ? 'bg-[#250001] text-[#f3f3f3] shadow-inner border-l-4 border-[#a50104]' : 'text-gray-300 hover:bg-[#250001] hover:text-[#f3f3f3]' }} rounded-xl font-medium transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            List of students
                        </a>
                    </nav>
                </div>
                
                <div class="p-4 border-t border-[#250001]">
                    <div class="flex items-center px-4 py-3 text-sm text-[#f3f3f3]">
                        <div class="w-8 h-8 rounded-full bg-[#a50104] text-white flex items-center justify-center font-bold mr-3 shadow-md text-xs">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-bold truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-300">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('logout') }}" class="mt-2 px-4">
                        @csrf
                        <button type="submit" class="w-full text-left text-xs text-gray-300 hover:text-[#f3f3f3] transition-colors">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Secure Logout
                        </button>
                    </form>
                </div>
            </aside>

            <main class="min-w-0 min-h-screen flex-1 overflow-y-auto">
                <header class="sticky top-0 z-30 flex min-h-16 items-center gap-3 border-b border-gray-200 bg-white px-4 shadow-sm lg:hidden">
                    <button type="button" @click="sidebarOpen = true" aria-label="Open navigation menu" aria-controls="staff-sidebar" :aria-expanded="sidebarOpen.toString()" class="inline-flex shrink-0 items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="min-w-0 truncate text-lg font-bold text-[#250001]">{{ $header ?? 'Staff Portal' }}</h1>
                </header>
                @if (isset($header))
                    <header class="hidden h-20 bg-white border-b border-gray-200 lg:flex items-center justify-between px-8 shadow-sm">
                        <h1 class="text-2xl font-bold text-[#250001]">{{ $header }}</h1>
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-medium text-gray-500">Academic Year 2025-2026</span>
                        </div>
                    </header>
                @endif

                {{ $slot }}
            </main>
        </div>
        @livewireScripts
    </body>
</html>
