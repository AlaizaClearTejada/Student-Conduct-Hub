<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth overflow-x-clip">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="SCMS: Student Conduct Management System - Promoting Accountability, Consistency, and Student Growth through Digital Integrity">

        <title>SCMS - Student Conduct Management System | Cagayan State University</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- AOS Animation Library -->
        <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-platinum text-mahogany overflow-x-clip">
        <!-- Header / Navigation -->
        <header class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-sm shadow-sm border-b-2 border-mahogany">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <!-- University Branding -->
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-3">
                            <!-- CSU Logo -->
                            <img src="{{ asset('LOGO/csu.png') }}" alt="Cagayan State University Logo" class="h-14 w-14 object-contain">
                            <!-- OSDW Logo -->
                            <img src="{{ asset('LOGO/OSDW.png') }}" alt="Office of Student Development and Welfare Logo" class="h-14 w-14 object-contain">
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-bold text-black-cherry">CSUA Conduct Hub</h1>
                            <p class="text-xs text-gray-700">Student Conduct Management</p>
                        </div>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="flex items-center gap-3">
                        <!-- Language Switcher -->
                        <div class="flex items-center gap-0.5 border border-gray-200 rounded-lg p-0.5">
                            <a href="{{ route('language.switch', 'en') }}"
                               class="flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded {{ app()->getLocale() === 'en' ? 'bg-inferno text-white shadow-sm' : 'text-gray-500 hover:text-mahogany' }} transition-all">
                                🇺🇸 <span class="hidden sm:inline">EN</span>
                            </a>
                            <a href="{{ route('language.switch', 'fil') }}"
                               class="flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded {{ app()->getLocale() === 'fil' ? 'bg-inferno text-white shadow-sm' : 'text-gray-500 hover:text-mahogany' }} transition-all">
                                🇵🇭 <span class="hidden sm:inline">FIL</span>
                            </a>
                        </div>

                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-4 py-2 text-sm font-medium text-mahogany hover:text-inferno transition-colors">
                                    {{ __('welcome.nav.dashboard') }}
                                </a>
                            @else
                                <a href="#faq" class="hidden md:block px-4 py-2 text-sm font-medium text-mahogany hover:text-inferno transition-colors">
                                    {{ __('welcome.nav.faq') }}
                                </a>
                                <a href="#support" class="hidden md:block px-4 py-2 text-sm font-medium text-mahogany hover:text-inferno transition-colors">
                                    {{ __('welcome.nav.support') }}
                                </a>
                                <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-white bg-inferno hover:bg-black-cherry rounded-lg transition-colors shadow-md">
                                    {{ __('welcome.nav.login') }}
                                </a>
                            @endauth
                        @endif
                    </nav>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="min-h-screen box-border flex items-center pt-24 pb-10 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-platinum via-white to-gray-100">
            <div class="max-w-7xl mx-auto w-full">
                <div class="text-center mb-12">
                    <!-- System Title -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-inferno text-white rounded-full text-sm font-semibold mb-6 shadow-md" data-aos="fade-down">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        {{ __('welcome.hero.badge') }}
                    </div>
                    
                    <h1 class="text-5xl md:text-6xl font-bold text-black-cherry mb-6" data-aos="fade-up" data-aos-delay="100">
                        {{ __('welcome.hero.title_line1') }}<br/>
                        <span class="text-inferno">{{ __('welcome.hero.title_line2') }}</span>
                    </h1>
                    
                    <!-- Mission Statement -->
                    <p class="text-xl text-gray-700 max-w-3xl mx-auto mb-8" data-aos="fade-up" data-aos-delay="200">
                        {{ __('welcome.hero.tagline') }}
                    </p>

                    <!-- Security Badges -->
                    <div class="flex flex-wrap items-center justify-center gap-4 text-sm text-gray-700">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-inferno" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">{{ __('welcome.hero.badge_ssl') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-inferno" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">{{ __('welcome.hero.badge_mfa') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-inferno" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium">{{ __('welcome.hero.badge_privacy') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- System Announcements & Quick Links -->
        <section class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- System Announcements -->
                    <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100" data-aos="fade-right">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-mahogany text-platinum rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-black-cherry">{{ __('welcome.announcements.title') }}</h2>
                        </div>
                        <div class="space-y-4">
                            <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
                                <p class="text-sm font-medium text-blue-900">{{ __('welcome.announcements.maintenance.title') }}</p>
                                <p class="text-sm text-blue-700 mt-1">{{ __('welcome.announcements.maintenance.desc') }}</p>
                            </div>
                            <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded">
                                <p class="text-sm font-medium text-green-900">{{ __('welcome.announcements.policy.title') }}</p>
                                <p class="text-sm text-green-700 mt-1">{{ __('welcome.announcements.policy.desc') }}</p>
                            </div>
                            <div class="p-4 bg-purple-50 border-l-4 border-purple-500 rounded">
                                <p class="text-sm font-medium text-purple-900">{{ __('welcome.announcements.feature.title') }}</p>
                                <p class="text-sm text-purple-700 mt-1">{{ __('welcome.announcements.feature.desc') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="bg-white rounded-2xl shadow-lg p-8 border border-gray-100" data-aos="fade-left">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-mahogany text-platinum rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-black-cherry">{{ __('welcome.quick_links.title') }}</h2>
                        </div>
                        <div class="space-y-3">
                            <a href="#" class="flex items-center justify-between p-4 bg-platinum rounded-lg hover:bg-white hover:border-inferno border-2 border-transparent transition-all group">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-inferno" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <span class="font-medium text-black-cherry">{{ __('welcome.quick_links.handbook') }}</span>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-inferno group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            <a href="#" class="flex items-center justify-between p-4 bg-platinum rounded-lg hover:bg-white hover:border-inferno border-2 border-transparent transition-all group">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-inferno" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span class="font-medium text-black-cherry">{{ __('welcome.quick_links.privacy') }}</span>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-inferno group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            <a href="#" class="flex items-center justify-between p-4 bg-platinum rounded-lg hover:bg-white hover:border-inferno border-2 border-transparent transition-all group">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-inferno" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="font-medium text-black-cherry">{{ __('welcome.quick_links.report') }}</span>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-inferno group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            <a href="#" class="flex items-center justify-between p-4 bg-platinum rounded-lg hover:bg-white hover:border-inferno border-2 border-transparent transition-all group">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-inferno" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="font-medium text-black-cherry">{{ __('welcome.quick_links.faqs') }}</span>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-inferno group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section id="faq" class="py-16 px-4 sm:px-6 lg:px-8 bg-platinum">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12" data-aos="fade-up">
                    <h2 class="text-4xl font-bold text-black-cherry mb-4">{{ __('welcome.faq.title') }}</h2>
                    <p class="text-lg text-gray-700">{{ __('welcome.faq.subtitle') }}</p>
                </div>
                
                <div class="space-y-4">
                    @foreach(__('welcome.faq.items') as $i => $item)
                    <details class="group bg-white rounded-xl shadow-md border-2 border-platinum overflow-hidden open:border-inferno transition-all" data-aos="fade-up" data-aos-delay="{{ 100 + $i * 50 }}">
                        <summary class="px-6 py-5 text-left flex justify-between items-center cursor-pointer hover:bg-platinum/30 transition-colors list-none">
                            <span class="font-semibold text-mahogany pr-4 transition-colors group-open:text-inferno">{{ $item['q'] }}</span>
                            <svg class="w-5 h-5 text-inferno flex-shrink-0 transition-transform duration-200 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="px-6 pb-5 bg-platinum">
                            <p class="text-gray-700 text-sm leading-relaxed">{{ $item['a'] }}</p>
                        </div>
                    </details>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Support Section -->
        <section id="support" class="py-16 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-inferno via-black-cherry to-mahogany">
            <div class="max-w-4xl mx-auto text-center" data-aos="zoom-in">
                <h2 class="text-4xl font-bold text-white mb-4">{{ __('welcome.support.title') }}</h2>
                <p class="text-xl text-platinum mb-8">{{ __('welcome.support.subtitle') }}</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-white" data-aos="fade-up" data-aos-delay="100">
                        <svg class="w-8 h-8 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <p class="font-medium mb-1">{{ __('welcome.support.email.label') }}</p>
                        <p class="text-sm text-platinum">{{ __('welcome.support.email.value') }}</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-white" data-aos="fade-up" data-aos-delay="200">
                        <svg class="w-8 h-8 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <p class="font-medium mb-1">{{ __('welcome.support.phone.label') }}</p>
                        <p class="text-sm text-platinum">{{ __('welcome.support.phone.value') }}</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-white" data-aos="fade-up" data-aos-delay="300">
                        <svg class="w-8 h-8 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-medium mb-1">{{ __('welcome.support.hours.label') }}</p>
                        <p class="text-sm text-platinum">{{ __('welcome.support.hours.value') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Session Security Notice -->
        <div id="security-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/50 px-4" role="dialog" aria-modal="true" aria-labelledby="security-modal-title">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-yellow-100 text-yellow-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 id="security-modal-title" class="font-semibold text-yellow-900">{{ __('welcome.security_notice.title') }}</h2>
                        <p class="mt-1 text-sm leading-relaxed text-yellow-800">{{ __('welcome.security_notice.desc') }}</p>
                    </div>
                    <button type="button" data-security-close class="rounded-lg p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700" aria-label="{{ __('welcome.security_notice.dismiss') }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <button type="button" data-security-close class="mt-6 w-full rounded-lg bg-inferno px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-black-cherry">
                    {{ __('welcome.security_notice.dismiss') }}
                </button>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-mahogany text-gray-300 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                    <!-- About -->
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-10 w-10 bg-gradient-to-br from-inferno to-black-cherry rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-sm">SCMS</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-white">{{ __('welcome.footer.system_name') }}</h3>
                                <p class="text-xs text-gray-400">{{ __('welcome.footer.university') }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-400 mb-4">
                            {{ __('welcome.footer.tagline') }}
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h4 class="font-semibold text-white mb-4">{{ __('welcome.footer.resources') }}</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white transition-colors">{{ __('welcome.footer.handbook') }}</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">{{ __('welcome.footer.policies') }}</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">{{ __('welcome.footer.appeal') }}</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">{{ __('welcome.footer.faqs') }}</a></li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div>
                        <h4 class="font-semibold text-white mb-4">{{ __('welcome.footer.legal') }}</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-white transition-colors">{{ __('welcome.footer.privacy') }}</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">{{ __('welcome.footer.terms') }}</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">{{ __('welcome.footer.data') }}</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">{{ __('welcome.footer.accessibility') }}</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom Footer -->
                <div class="pt-8 border-t border-black-cherry">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <p class="text-sm text-gray-400">
                            {!! __('welcome.footer.copyright', ['year' => date('Y')]) !!}
                        </p>
                        <div class="flex items-center gap-4 text-sm text-gray-400">
                            <span>{{ __('welcome.footer.version') }}</span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-inferno" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                {{ __('welcome.footer.online') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <!-- AOS Animation Script -->
        <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
        <script>
            AOS.init({
                duration: 800,
                offset: 100,
                once: true,
                easing: 'ease-in-out'
            });

            const securityModal = document.getElementById('security-modal');
            const closeSecurityModal = () => {
                securityModal.classList.add('hidden');
                securityModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            };

            document.querySelectorAll('[data-security-close]').forEach((button) => {
                button.addEventListener('click', closeSecurityModal);
            });

            securityModal.addEventListener('click', (event) => {
                if (event.target === securityModal) {
                    closeSecurityModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !securityModal.classList.contains('hidden')) {
                    closeSecurityModal();
                }
            });

            window.addEventListener('load', () => {
                securityModal.classList.remove('hidden');
                securityModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            });
        </script>
    </body>
</html>
