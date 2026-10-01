<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <style>
            @keyframes pulse {
                0%, 100% {
                    opacity: 1;
                }
                50% {
                    opacity: 0.5;
                }
            }
            .animate-pulse {
                animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex bg-[#f3f3f3]" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
            
            <div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden" aria-hidden="true"></div>
            <aside id="student-sidebar" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] bg-[#250001] shadow-2xl flex flex-col justify-between transform transition-transform duration-200 lg:static lg:max-w-none lg:translate-x-0">
                <div>
                    <div class="h-20 flex items-center px-8 border-b border-[#590004]">
                        <img src="{{ asset('LOGO/csu.png') }}" alt="CSU Logo" class="h-12 w-12 object-contain">
                        <span class="ml-3 text-[#f3f3f3] font-bold text-lg tracking-wide">Student Portal</span>
                    </div>
                    
                    <nav @click="if ($event.target.closest('a')) sidebarOpen = false" class="mt-8 px-4 space-y-2 overflow-y-auto">
                        <a href="{{ route('student.dashboard') }}" 
                           class="flex items-center px-4 py-3 {{ request()->routeIs('student.dashboard') ? 'bg-[#590004] text-[#f3f3f3]' : 'text-gray-400 hover:bg-[#590004] hover:text-[#f3f3f3]' }} rounded-xl font-medium transition-colors {{ request()->routeIs('student.dashboard') ? 'shadow-inner' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            My Dashboard
                        </a>
                        
                        <a href="{{ route('student.records') }}" 
                           class="flex items-center px-4 py-3 {{ request()->routeIs('student.records') ? 'bg-[#590004] text-[#f3f3f3]' : 'text-gray-400 hover:bg-[#590004] hover:text-[#f3f3f3]' }} rounded-xl font-medium transition-colors {{ request()->routeIs('student.records') ? 'shadow-inner' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Conduct Records
                        </a>
                        
                        <a href="{{ route('student.offense-rules') }}" 
                           class="flex items-center px-4 py-3 {{ request()->routeIs('student.offense-rules') ? 'bg-[#590004] text-[#f3f3f3]' : 'text-gray-400 hover:bg-[#590004] hover:text-[#f3f3f3]' }} rounded-xl font-medium transition-colors {{ request()->routeIs('student.offense-rules') ? 'shadow-inner' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            University Policy
                        </a>

                        <a href="{{ route('student.profile') }}"
                           class="flex items-center px-4 py-3 {{ request()->routeIs('student.profile') ? 'bg-[#590004] text-[#f3f3f3]' : 'text-gray-400 hover:bg-[#590004] hover:text-[#f3f3f3]' }} rounded-xl font-medium transition-colors {{ request()->routeIs('student.profile') ? 'shadow-inner' : '' }}">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            My Profile
                        </a>
                    </nav>
                </div>
                
                <div class="p-4 border-t border-[#590004]">
                    <div class="flex items-center px-4 py-3 text-sm text-[#f3f3f3]">
                        <div class="w-8 h-8 rounded-full bg-[#f3f3f3] text-[#250001] flex items-center justify-center font-bold mr-3 text-xs">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="flex-1">
                            <p class="font-bold truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-400">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('logout') }}" class="mt-2 px-4">
                        @csrf
                        <button type="submit" class="w-full text-left text-xs text-gray-400 hover:text-[#f3f3f3] transition-colors">
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
                    <button type="button" @click="sidebarOpen = true" aria-label="Open navigation menu" aria-controls="student-sidebar" :aria-expanded="sidebarOpen.toString()" class="inline-flex shrink-0 items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="min-w-0 truncate text-lg font-bold text-[#250001]">{{ $header ?? 'Student Portal' }}</h1>
                </header>
                @if (isset($header))
                    <header class="hidden h-20 bg-white border-b border-gray-200 lg:flex items-center justify-between px-8 shadow-sm">
                        <h1 class="text-2xl font-bold text-[#250001]">{{ $header }}</h1>
                    </header>
                @endif

                {{ $slot }}
            </main>
        </div>
        @livewireScripts
    </body>
</html>
