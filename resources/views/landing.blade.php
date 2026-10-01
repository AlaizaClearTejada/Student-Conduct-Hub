<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SCMS: Student Conduct Management System - Promoting Accountability, Consistency, and Student Growth">

    <title>SCMS - Modern Student Conduct Management</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans bg-platinum text-mahogany">

    <!-- Header -->
    <header class="h-[66px] flex items-center justify-between px-[10%] border-b border-gray-300 bg-white">
        <div class="flex items-center gap-3">
            <img src="{{ asset('LOGO/csu.png') }}" alt="CSU Logo" class="h-10 w-10 object-contain">
            <span class="text-lg font-bold text-black-cherry">SCMS</span>
        </div>
        <div class="flex items-center gap-4 text-sm text-gray-600">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-inferno" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>SSL Secured</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-inferno" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>Privacy Compliant</span>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="h-[calc(100vh-66px)] flex items-center justify-center px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-platinum via-white to-gray-100 border-b border-gray-300">
        <div class="max-w-7xl mx-auto w-full">
            <div class="text-center">
                <!-- System Title -->
                <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-inferno text-white rounded-full text-xs font-semibold mb-3 shadow-md">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Secure Institutional Access
                </div>
                
                <h1 class="text-3xl md:text-4xl font-bold text-black-cherry mb-3">
                    Student Conduct<br>
                    <span class="text-inferno">Management System</span>
                </h1>
                
                <!-- Mission Statement -->
                <p class="text-base text-gray-700 max-w-2xl mx-auto mb-4">
                    Promoting Accountability, Consistency, and Student Growth through Digital Integrity
                </p>

                <!-- System Impact Stats -->
                <div class="max-w-3xl mx-auto mb-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-white rounded-lg p-3 shadow-lg border-2 border-platinum hover:border-inferno transition-all">
                            <div class="flex items-center justify-center gap-1.5 mb-0.5">
                                <svg class="w-4 h-4 text-inferno" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-xl font-bold text-black-cherry">98%</span>
                            </div>
                            <p class="text-xs text-gray-700 text-center font-medium">Resolution Rate</p>
                        </div>
                        <div class="bg-white rounded-lg p-3 shadow-lg border-2 border-platinum hover:border-inferno transition-all">
                            <div class="flex items-center justify-center gap-1.5 mb-0.5">
                                <svg class="w-4 h-4 text-inferno" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-xl font-bold text-black-cherry">100%</span>
                            </div>
                            <p class="text-xs text-gray-700 text-center font-medium">Policy Consistency</p>
                        </div>
                        <div class="bg-white rounded-lg p-3 shadow-lg border-2 border-platinum hover:border-inferno transition-all">
                            <div class="flex items-center justify-center gap-1.5 mb-0.5">
                                <svg class="w-4 h-4 text-inferno" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-xl font-bold text-black-cherry">Real-time</span>
                            </div>
                            <p class="text-xs text-gray-700 text-center font-medium">Transparency</p>
                        </div>
                    </div>
                </div>

                <!-- Security Badges -->
                <div class="flex flex-wrap items-center justify-center gap-3 text-xs text-gray-700">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-inferno" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">SSL Encrypted</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-inferno" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">MFA Protected</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-inferno" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">Privacy Compliant</span>
                    </div>
                </div>
            </div>
        </div>
                        <span class="font-medium">Privacy Compliant</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- The Problem Section -->
    <section class="px-[10%] py-16 border-b border-gray-300 text-center">
        <h2 class="text-3xl font-bold text-black-cherry mb-8">The Problem</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white border border-gray-300 rounded-xl p-6 shadow-sm">
                <div class="w-12 h-12 bg-inferno/10 text-inferno rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-lg text-mahogany mb-2">Inconsistent Policies</h3>
                <p class="text-gray-600 text-sm">Manual processes lead to inconsistent application of conduct policies across departments.</p>
            </div>
            <div class="bg-white border border-gray-300 rounded-xl p-6 shadow-sm">
                <div class="w-12 h-12 bg-inferno/10 text-inferno rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-lg text-mahogany mb-2">Paper-Based Records</h3>
                <p class="text-gray-600 text-sm">Physical files are difficult to search, track, and secure, leading to lost information.</p>
            </div>
            <div class="bg-white border border-gray-300 rounded-xl p-6 shadow-sm">
                <div class="w-12 h-12 bg-inferno/10 text-inferno rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-lg text-mahogany mb-2">Slow Resolution</h3>
                <p class="text-gray-600 text-sm">Without automation, case resolution takes weeks instead of days.</p>
            </div>
        </div>
    </section>

    <!-- How We Solve This Section -->
    <section class="px-[10%] py-16 border-b border-gray-300 text-center bg-white/70">
        <h2 class="text-3xl font-bold text-black-cherry mb-8">How We Solve This</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-platinum border border-gray-300 rounded-xl p-6 shadow-sm">
                <div class="w-12 h-12 bg-mahogany text-white rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-lg text-mahogany mb-2">Policy Automation</h3>
                <p class="text-gray-600 text-sm">Automatic progressive sanctioning ensures fair, consistent treatment for all students.</p>
            </div>
            <div class="bg-platinum border border-gray-300 rounded-xl p-6 shadow-sm">
                <div class="w-12 h-12 bg-mahogany text-white rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                    </svg>
                </div>
                <h3 class="font-semibold text-lg text-mahogany mb-2">Digital Records</h3>
                <p class="text-gray-600 text-sm">Secure, searchable database with complete audit trails and instant access.</p>
            </div>
            <div class="bg-platinum border border-gray-300 rounded-xl p-6 shadow-sm">
                <div class="w-12 h-12 bg-mahogany text-white rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-lg text-mahogany mb-2">Automated Workflows</h3>
                <p class="text-gray-600 text-sm">Streamlined processes reduce resolution time from weeks to days.</p>
            </div>
        </div>
    </section>

    <!-- Trusted By Section -->
    <section class="px-[10%] py-16 border-b border-gray-300 text-center">
        <h2 class="text-3xl font-bold text-black-cherry mb-8">Trusted By</h2>
        <div class="flex flex-wrap items-center justify-center gap-8">
            <div class="w-24 h-12 bg-gray-200 border border-gray-300 rounded flex items-center justify-center text-gray-400 text-xs">Logo</div>
            <div class="w-24 h-12 bg-gray-200 border border-gray-300 rounded flex items-center justify-center text-gray-400 text-xs">Logo</div>
            <div class="w-24 h-12 bg-gray-200 border border-gray-300 rounded flex items-center justify-center text-gray-400 text-xs">Logo</div>
            <div class="w-24 h-12 bg-gray-200 border border-gray-300 rounded flex items-center justify-center text-gray-400 text-xs">Logo</div>
            <div class="w-24 h-12 bg-gray-200 border border-gray-300 rounded flex items-center justify-center text-gray-400 text-xs">Logo</div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="px-[10%] py-16 border-b border-gray-300 text-center bg-white/70">
        <h2 class="text-3xl font-bold text-black-cherry mb-8">How It Works</h2>
        <div class="flex flex-col md:flex-row items-center justify-center gap-4 md:gap-6">
            <div class="bg-platinum border border-gray-400 rounded-xl px-6 py-4 text-center">
                <div class="w-8 h-8 bg-inferno text-white rounded-full flex items-center justify-center mx-auto mb-2 text-sm font-bold">1</div>
                <p class="font-medium text-mahogany">Report Incident</p>
            </div>
            <div class="hidden md:block text-2xl text-gray-400">&rarr;</div>
            <div class="md:hidden text-2xl text-gray-400">&darr;</div>
            <div class="bg-platinum border border-gray-400 rounded-xl px-6 py-4 text-center">
                <div class="w-8 h-8 bg-inferno text-white rounded-full flex items-center justify-center mx-auto mb-2 text-sm font-bold">2</div>
                <p class="font-medium text-mahogany">Review & Verify</p>
            </div>
            <div class="hidden md:block text-2xl text-gray-400">&rarr;</div>
            <div class="md:hidden text-2xl text-gray-400">&darr;</div>
            <div class="bg-platinum border border-gray-400 rounded-xl px-6 py-4 text-center">
                <div class="w-8 h-8 bg-inferno text-white rounded-full flex items-center justify-center mx-auto mb-2 text-sm font-bold">3</div>
                <p class="font-medium text-mahogany">Resolve & Track</p>
            </div>
        </div>
    </section>

    <!-- Product Feature Section -->
    <section class="flex flex-col md:flex-row items-center justify-center gap-10 px-[10%] py-16 border-b border-gray-300 text-left">
        <div class="w-full max-w-sm h-52 bg-gray-200 border border-gray-400 rounded-xl flex items-center justify-center text-gray-500">
            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>
        <div class="max-w-md">
            <h3 class="text-2xl font-bold text-black-cherry mb-4">Powerful Dashboard</h3>
            <ul class="space-y-3">
                <li class="flex items-center gap-3 text-gray-700 pb-3 border-b border-gray-200">
                    <svg class="w-5 h-5 text-inferno flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Real-time incident tracking & status updates
                </li>
                <li class="flex items-center gap-3 text-gray-700 pb-3 border-b border-gray-200">
                    <svg class="w-5 h-5 text-inferno flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Role-based access control & permissions
                </li>
                <li class="flex items-center gap-3 text-gray-700 pb-3 border-b border-gray-200">
                    <svg class="w-5 h-5 text-inferno flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Comprehensive analytics & reporting
                </li>
                <li class="flex items-center gap-3 text-gray-700">
                    <svg class="w-5 h-5 text-inferno flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Multi-factor authentication security
                </li>
            </ul>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="px-[10%] py-16 border-b border-gray-300 text-center">
        <h2 class="text-3xl font-bold text-black-cherry mb-8">Frequently Asked Questions</h2>
        <div class="max-w-2xl mx-auto space-y-4 text-left">
            <details class="bg-white border border-gray-300 rounded-xl p-4 cursor-pointer group">
                <summary class="font-semibold text-mahogany flex justify-between items-center">
                    <span>How secure is the platform?</span>
                    <span class="text-inferno text-xl group-open:rotate-45 transition-transform">+</span>
                </summary>
                <p class="mt-3 text-gray-600 text-sm">Our platform uses enterprise-grade security including SSL encryption, multi-factor authentication, role-based access control, and complete audit trails. All data is encrypted at rest and in transit.</p>
            </details>
            <details class="bg-white border border-gray-300 rounded-xl p-4 cursor-pointer group">
                <summary class="font-semibold text-mahogany flex justify-between items-center">
                    <span>Can we customize the policies?</span>
                    <span class="text-inferno text-xl group-open:rotate-45 transition-transform">+</span>
                </summary>
                <p class="mt-3 text-gray-600 text-sm">Yes! The system is fully configurable to match your institution's specific conduct policies, offense categories, and progressive sanctioning rules. Our team helps with initial setup.</p>
            </details>
            <details class="bg-white border border-gray-300 rounded-xl p-4 cursor-pointer group">
                <summary class="font-semibold text-mahogany flex justify-between items-center">
                    <span>How long does implementation take?</span>
                    <span class="text-inferno text-xl group-open:rotate-45 transition-transform">+</span>
                </summary>
                <p class="mt-3 text-gray-600 text-sm">Most institutions are fully operational within 2-4 weeks. This includes data migration, policy configuration, staff training, and testing before going live.</p>
            </details>
        </div>
    </section>

    <!-- Bottom CTA Section -->
    <section class="px-[10%] py-16 border-b border-gray-300 text-center bg-gradient-to-br from-black-cherry to-mahogany text-white">
        <h2 class="text-3xl font-bold mb-4">Ready to Get Started?</h2>
        <p class="text-lg text-gray-200 mb-8 max-w-xl mx-auto">Transform your student conduct management today with our comprehensive digital platform.</p>
        <a href="{{ route('login') }}" class="inline-block px-8 py-4 bg-inferno text-white font-semibold rounded-lg hover:bg-white hover:text-inferno transition-colors shadow-lg text-lg">
            Schedule a Demo
        </a>
        <p class="text-sm text-gray-300 mt-4">No commitment required. Cancel anytime.</p>
    </section>

    <!-- Footer -->
    <footer class="px-[10%] py-6 text-center bg-platinum">
        <div class="flex flex-wrap items-center justify-center gap-4 text-sm text-gray-600">
            <div class="flex items-center gap-2">
                <img src="{{ asset('LOGO/csu.png') }}" alt="CSU Logo" class="h-6 w-6 object-contain">
                <span class="font-semibold text-mahogany">SCMS</span>
            </div>
            <span>&bull;</span>
            <a href="#" class="hover:text-inferno transition-colors">Privacy</a>
            <span>&bull;</span>
            <a href="#" class="hover:text-inferno transition-colors">Terms</a>
            <span>&bull;</span>
            <a href="#" class="hover:text-inferno transition-colors">Contact</a>
        </div>
        <p class="text-xs text-gray-500 mt-3">&copy; {{ date('Y') }} Cagayan State University. All rights reserved.</p>
    </footer>

</body>
</html>
