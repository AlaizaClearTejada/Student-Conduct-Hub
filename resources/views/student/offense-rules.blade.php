<x-student-app-layout>
    <x-slot name="header">
        University Policy - Student Conduct Manual
    </x-slot>

    {{-- Offense data injected via script tag to avoid HTML-escaping issues --}}
    <script>
        window.__offenseData = {!! $offenseJson !!};
    </script>

    <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6"
         x-data="conductManual()">

            {{-- Header / summary card --}}
        <div class="relative overflow-hidden rounded-2xl shadow-xl"
             style="background: linear-gradient(135deg, #1a0000 0%, #250001 30%, #590004 70%, #3d0003 100%);">
            <div class="absolute inset-0 opacity-10"
                 style="background-image: repeating-linear-gradient(45deg,#fff 0,#fff 1px,transparent 0,transparent 50%); background-size:20px 20px;"></div>
            <div class="relative p-8 md:p-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2.5 bg-white/15 rounded-xl">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold tracking-widest text-white/60 uppercase">Official University Document</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white leading-tight mb-3">
                        CSU Student Conduct Manual
                    </h1>
                    <p class="text-gray-300 leading-relaxed max-w-2xl text-sm md:text-base">
                        This document outlines all offense codes, progressive sanctions, and disciplinary procedures
                        as mandated by the Cagayan State University Student Handbook. Every student is expected to
                        know and abide by these regulations.
                    </p>
                    <div class="flex flex-wrap items-center gap-4 mt-5 text-sm text-white/70">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                            </svg>
                            Last Updated: March 1, 2026
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                            </svg>
                            {{ $offenseRules->count() }} Offense Codes
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ $categories->count() }} Categories
                        </span>
                    </div>
                </div>
                {{-- Quick stats --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:min-w-[240px]">
                    @foreach($categories as $cat)
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 text-center">
                        <p class="text-2xl font-extrabold text-white">{{ $offenseRules->where('category', $cat)->count() }}</p>
                        <p class="text-xs text-white/70 font-medium mt-0.5 leading-tight">{{ $cat }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             SEARCH + FILTERS
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-5">

            {{-- Search --}}
            <div class="relative">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    type="text"
                    x-model="search"
                    placeholder="Search by offense code, title, or keyword..."
                    class="w-full pl-12 pr-4 py-3.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#590004] focus:border-transparent transition"
                />
                <template x-if="search.length">
                    <button @click="search = ''"
                            class="absolute inset-y-0 right-4 flex items-center text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </template>
            </div>

            {{-- Category filter --}}
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2.5">Category</p>
                <div class="flex flex-wrap gap-2">
                    <button @click="filterCategory('all')"
                            :class="activeCategory==='all'
                                ? 'bg-[#590004] text-white shadow-sm'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-lg text-xs font-semibold transition-all">
                        All ({{ $offenseRules->count() }})
                    </button>
                    @foreach($categories as $category)
                    <button @click="filterCategory('{{ $category }}')"
                            :class="activeCategory==='{{ $category }}'
                                ? 'bg-[#590004] text-white shadow-sm'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-4 py-2 rounded-lg text-xs font-semibold transition-all">
                        {{ $category }} ({{ $offenseRules->where('category', $category)->count() }})
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Severity filter --}}
            <div class="border-t border-gray-100 pt-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2.5">Severity Level</p>
                <div class="flex flex-wrap gap-2">
                    <button @click="filterSeverity('all')"
                            :class="activeSeverity==='all'
                                ? 'ring-2 ring-gray-600 bg-gray-700 text-white'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all">All</button>
                    @foreach($severityLevels as $level)
                    <button @click="filterSeverity('{{ $level }}')"
                            :class="activeSeverity==='{{ $level }}'
                                ? '{{ $level === 'Minor' ? 'ring-2 ring-green-600 bg-green-600 text-white' : ($level === 'Moderate' ? 'ring-2 ring-yellow-500 bg-yellow-500 text-white' : ($level === 'Major' ? 'ring-2 ring-orange-500 bg-orange-500 text-white' : 'ring-2 ring-red-600 bg-red-600 text-white')) }}'
                                : '{{ $level === 'Minor' ? 'bg-green-50 text-green-700 hover:bg-green-100' : ($level === 'Moderate' ? 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' : ($level === 'Major' ? 'bg-orange-50 text-orange-700 hover:bg-orange-100' : 'bg-red-50 text-red-700 hover:bg-red-100')) }}'"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold transition-all">
                        {{ $level }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Result count --}}
            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                <p class="text-sm text-gray-500">
                    Showing <span class="font-bold text-[#250001]" x-text="filteredOffenses.length"></span> of
                    <span class="font-bold">{{ $offenseRules->count() }}</span> offense(s)
                </p>
                <button x-show="activeCategory!=='all' || activeSeverity!=='all' || search.length"
                        @click="resetFilters()"
                        class="text-xs text-[#590004] font-semibold hover:underline">
                    Clear all filters
                </button>
            </div>
        </div>

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             OFFENSE CARDS (Alpine x-for - fully reactive)
        â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        <div class="space-y-3" id="offense-list">
            <template x-for="offense in filteredOffenses" :key="offense.code">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden
                        transition-all duration-200 hover:shadow-md hover:border-[#590004]/30"
                 x-data="{ expanded: false }">

                {{-- Card Header (always visible) --}}
                <button @click="expanded = !expanded"
                        class="w-full text-left px-6 py-5 flex items-start gap-4 focus:outline-none">

                    {{-- Offense code tile --}}
                    <div class="flex-shrink-0 w-16 h-16 rounded-xl flex items-center justify-center font-extrabold text-sm"
                         :class="{
                             'bg-green-50 text-green-800 border border-green-200':  offense.severity_level === 'Minor',
                             'bg-yellow-50 text-yellow-800 border border-yellow-200': offense.severity_level === 'Moderate',
                             'bg-orange-50 text-orange-800 border border-orange-200': offense.severity_level === 'Major',
                             'bg-red-50 text-red-800 border border-red-200': !['Minor','Moderate','Major'].includes(offense.severity_level)
                         }"
                         x-text="offense.code">
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            {{-- Category badge --}}
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                  :class="{
                                      'bg-purple-100 text-purple-800': offense.category === 'Academic',
                                      'bg-rose-100 text-rose-800':     offense.category === 'Behavioral',
                                      'bg-blue-100 text-blue-800':     offense.category === 'Procedural',
                                      'bg-orange-100 text-orange-800': offense.category === 'Safety',
                                      'bg-indigo-100 text-indigo-800': !['Academic','Behavioral','Procedural','Safety'].includes(offense.category)
                                  }"
                                  x-text="offense.category">
                            </span>
                            {{-- Severity badge --}}
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                  :class="{
                                      'bg-green-100 text-green-800':  offense.severity_level === 'Minor',
                                      'bg-yellow-100 text-yellow-800': offense.severity_level === 'Moderate',
                                      'bg-orange-100 text-orange-800': offense.severity_level === 'Major',
                                      'bg-red-100 text-red-800': !['Minor','Moderate','Major'].includes(offense.severity_level)
                                  }"
                                  x-text="offense.severity_level">
                            </span>
                            {{-- SDT badge --}}
                            <span x-show="offense.requires_tribunal"
                                  class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#250001]/10 text-[#250001]">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/>
                                </svg>
                                SDT Required
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-[#250001] leading-snug" x-text="offense.title"></h3>
                        <p class="text-sm text-gray-500 mt-0.5 line-clamp-1" x-text="offense.description"></p>
                    </div>

                    {{-- Chevron --}}
                    <div class="flex-shrink-0 mt-1">
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                             :class="{ 'rotate-180': expanded }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>

                {{-- Expanded Detail --}}
                <div x-show="expanded"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="border-t border-gray-100 px-6 pb-6 pt-5 space-y-5">

                    {{-- Description --}}
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Description</p>
                        <p class="text-sm text-gray-700 leading-relaxed" x-text="offense.description"></p>
                    </div>

                    {{-- Progressive Sanctions --}}
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Progressive Sanctions</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-6 h-6 rounded-full bg-green-200 text-green-800 text-xs font-black flex items-center justify-center">1</span>
                                    <span class="text-xs font-bold text-green-800 uppercase tracking-wide">1st Offense</span>
                                </div>
                                <p class="text-sm text-gray-700" x-text="offense.first_offense_sanction || offense.standard_sanction || '-'"></p>
                            </div>
                            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-6 h-6 rounded-full bg-yellow-200 text-yellow-800 text-xs font-black flex items-center justify-center">2</span>
                                    <span class="text-xs font-bold text-yellow-800 uppercase tracking-wide">2nd Offense</span>
                                </div>
                                <p class="text-sm text-gray-700" x-text="offense.second_offense_sanction || offense.standard_sanction || '-'"></p>
                            </div>
                            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-6 h-6 rounded-full bg-red-200 text-red-800 text-xs font-black flex items-center justify-center">3</span>
                                    <span class="text-xs font-bold text-red-800 uppercase tracking-wide">3rd Offense</span>
                                </div>
                                <p class="text-sm text-gray-700" x-text="offense.third_offense_sanction || offense.standard_sanction || '-'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Meta row --}}
                    <div class="flex flex-wrap gap-4 pt-1">
                        <div x-show="offense.legal_reference" class="flex items-center gap-2 text-xs text-gray-500">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="font-medium">Legal Reference:</span>
                            <span class="text-gray-700" x-text="offense.legal_reference"></span>
                        </div>
                        <div x-show="offense.gravity" class="flex items-center gap-2 text-xs text-gray-500">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                            <span class="font-medium">Gravity:</span>
                            <span class="capitalize text-gray-700" x-text="offense.gravity"></span>
                        </div>
                    </div>
                </div>
            </div>
            </template>

            {{-- No results from filter --}}
            <div x-show="filteredOffenses.length === 0"
                 class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <p class="text-gray-500 font-medium">No offenses match your current filters.</p>
                <button @click="resetFilters()"
                        class="mt-4 text-sm text-[#590004] font-semibold hover:underline">
                    Clear all filters
                </button>
            </div>
        </div>

    </div>{{-- /main wrapper --}}


    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         CONDUCT POLICY CHATBOT - AI-Powered (Claude via Laravel proxy)
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <div x-data="chatbot()" x-cloak class="fixed bottom-3 right-3 sm:bottom-6 sm:right-6 z-50 flex max-w-[calc(100vw-1.5rem)] flex-col items-end gap-3">

        {{-- Chat window --}}
        <div x-show="open"
             x-transition:enter="transition duration-300"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-4"
             class="w-full max-w-[370px] bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden"
             style="max-height:min(580px, calc(100dvh - 1.5rem)); transform-origin: bottom right;">

            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3.5 flex-shrink-0"
                 style="background: linear-gradient(135deg, #250001, #590004);">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white leading-none">JAM AI Chatbot</p>
                        <p class="text-xs text-white/60 mt-0.5" x-text="started ? studentName + ' · ' + (lang === 'fil' ? 'Filipino' : 'English') : 'AI-Powered - Claude'"></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button x-show="started" @click="confirmReset()"
                            title="Start over"
                            class="text-white/50 hover:text-white transition-colors p-1 rounded">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                    <button @click="open = false" class="text-white/60 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Tabs (only visible after started) --}}
            <div x-show="started" class="flex border-b border-gray-100 flex-shrink-0 bg-white">
                <button @click="activeTab = 'chat'"
                        :class="activeTab === 'chat' ? 'border-b-2 border-[#590004] text-[#590004] font-semibold' : 'text-gray-500 hover:text-gray-700'"
                        class="flex-1 py-2.5 text-xs transition-colors">
                    Chat
                </button>
                <button @click="activeTab = 'history'"
                        :class="activeTab === 'history' ? 'border-b-2 border-[#590004] text-[#590004] font-semibold' : 'text-gray-500 hover:text-gray-700'"
                        class="flex-1 py-2.5 text-xs transition-colors">
                    History
                    <span x-show="chatHistory.length > 0"
                          class="ml-1 inline-flex items-center justify-center w-4 h-4 bg-gray-200 rounded-full text-[9px] text-gray-600"
                          x-text="chatHistory.length"></span>
                </button>
            </div>

            {{-- LOGIN SCREEN --}}
            <div x-show="!started" class="flex-1 flex flex-col items-center justify-center p-6 bg-gray-50 gap-5">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-lg"
                     style="background: linear-gradient(135deg, #250001, #590004);">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div class="text-center">
                    <p class="font-bold text-gray-800 text-base">JAM AI Chatbot</p>
                    <p class="text-xs text-gray-500 mt-1">CSU AI Policy Assistant &middot; Powered by Claude AI</p>
                </div>
                <div class="w-full space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Your Name</label>
                        <input type="text"
                               x-model="studentName"
                               @keydown.enter.prevent="startChat()"
                               placeholder="Enter your name..."
                               class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#590004] focus:border-transparent transition"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-2">Language</label>
                        <div class="flex gap-2">
                            <button @click="lang = 'en'"
                                    :class="lang === 'en' ? 'bg-[#590004] text-white border-[#590004]' : 'bg-white text-gray-600 border-gray-200 hover:border-[#590004]'"
                                    class="flex-1 py-2 text-sm font-medium border rounded-xl transition-all">
                                &#x1F1FA;&#x1F1F8; English
                            </button>
                            <button @click="lang = 'fil'"
                                    :class="lang === 'fil' ? 'bg-[#590004] text-white border-[#590004]' : 'bg-white text-gray-600 border-gray-200 hover:border-[#590004]'"
                                    class="flex-1 py-2 text-sm font-medium border rounded-xl transition-all">
                                &#x1F1F5;&#x1F1ED; Filipino
                            </button>
                        </div>
                    </div>
                    <button @click="startChat()"
                            :disabled="!studentName.trim()"
                            class="w-full py-3 rounded-xl text-sm font-bold text-white transition-all disabled:opacity-40 disabled:cursor-not-allowed hover:opacity-90 active:scale-95"
                            style="background: linear-gradient(135deg, #250001, #590004);">
                        Start Chatting &#8594;
                    </button>
                </div>
            </div>

            {{-- CHAT TAB --}}
            <div x-show="started && activeTab === 'chat'" class="flex flex-col flex-1 min-h-0">
                {{-- Messages --}}
                <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50 min-h-0" x-ref="messageContainer">
                    <template x-for="(msg, i) in messages" :key="i">
                        <div :class="msg.role === 'assistant' ? 'flex items-start gap-2' : 'flex justify-end'">
                            <template x-if="msg.role === 'assistant'">
                                <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center mt-0.5"
                                     style="background: linear-gradient(135deg, #250001, #590004);">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                            </template>
                            <div :class="msg.role === 'assistant'
                                    ? 'bg-white border border-gray-200 text-gray-700 rounded-2xl rounded-tl-sm px-4 py-3 max-w-[85%] text-sm shadow-sm'
                                    : 'text-white rounded-2xl rounded-tr-sm px-4 py-3 max-w-[85%] text-sm'"
                                 :style="msg.role === 'user' ? 'background: linear-gradient(135deg, #250001, #590004)' : ''"
                                 x-html="msg.role === 'assistant' ? formatReply(msg.content) : escapeHtml(msg.content)">
                            </div>
                        </div>
                    </template>

                    {{-- Typing indicator --}}
                    <div x-show="typing" class="flex items-start gap-2">
                        <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center"
                             style="background: linear-gradient(135deg, #250001, #590004);">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm flex items-center gap-1.5">
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                        </div>
                    </div>
                </div>

                {{-- Quick prompts --}}
                <div x-show="messages.length <= 1" class="px-3 py-2 bg-white border-t border-gray-100 flex gap-1.5 overflow-x-auto no-scrollbar flex-shrink-0">
                    <template x-for="prompt in quickPrompts" :key="prompt">
                        <button @click="sendMessage(prompt)"
                                :disabled="typing"
                                class="flex-shrink-0 px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200
                                       text-gray-600 bg-gray-50 hover:bg-[#590004] hover:text-white hover:border-[#590004]
                                       transition-all whitespace-nowrap disabled:opacity-40"
                                x-text="prompt">
                        </button>
                    </template>
                </div>

                {{-- Input --}}
                <div class="p-3 bg-white border-t border-gray-100 flex gap-2 flex-shrink-0">
                    <input type="text"
                           x-model="userInput"
                           :disabled="typing"
                           @keydown.enter.prevent="sendMessage(userInput)"
                           :placeholder="lang === 'fil' ? 'Magtanong tungkol sa paglabag...' : 'Ask about any offense or rule...'"
                           class="flex-1 px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#590004] focus:border-transparent transition disabled:opacity-50"/>
                    <button @click="sendMessage(userInput)"
                            :disabled="!userInput.trim() || typing"
                            class="px-3.5 py-2.5 rounded-xl text-white transition-all disabled:opacity-40 disabled:cursor-not-allowed hover:opacity-90 flex-shrink-0"
                            style="background: linear-gradient(135deg, #250001, #590004)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- HISTORY TAB --}}
            <div x-show="started && activeTab === 'history'" class="flex-1 overflow-y-auto bg-gray-50 p-4 space-y-3 min-h-0">
                <template x-if="chatHistory.length === 0">
                    <div class="text-center py-8 text-gray-400 text-sm">
                        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        No conversation history yet.
                    </div>
                </template>
                <template x-for="(entry, i) in chatHistory.slice().reverse()" :key="i">
                    <div class="bg-white rounded-xl border border-gray-200 p-3 text-xs cursor-pointer hover:border-[#590004] transition-colors"
                         @click="loadHistory(entry)">
                        <p class="font-semibold text-gray-700 truncate" x-text="entry.preview"></p>
                        <p class="text-gray-400 mt-1" x-text="entry.date"></p>
                    </div>
                </template>
                <template x-if="chatHistory.length > 0">
                    <button @click="clearHistory()"
                            class="w-full mt-2 text-xs text-red-500 hover:text-red-700 py-2 hover:bg-red-50 rounded-lg transition-colors">
                        Clear all history
                    </button>
                </template>
            </div>
        </div>

        {{-- OSA Escalation Modal --}}
        <div x-show="showOsaModal"
             x-transition:enter="transition duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-60 flex items-center justify-center p-4"
             @click.self="showOsaModal = false">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-[#a50104]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-center font-bold text-gray-800 mb-2">Contact the OSA</h3>
                <p class="text-center text-sm text-gray-600 mb-5">The Office of Student Affairs can provide official guidance on disciplinary procedures and your rights as a student.</p>
                <div class="space-y-2">
                    <a href="mailto:osa@csu.edu.ph"
                       class="block w-full text-center py-3 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90"
                       style="background: linear-gradient(135deg, #250001, #590004);">
                        Email osa@csu.edu.ph
                    </a>
                    <button @click="showOsaModal = false"
                            class="block w-full text-center py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>

        {{-- Reset Confirm Modal --}}
        <div x-show="showResetModal"
             x-transition:enter="transition duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-black/50 z-60 flex items-center justify-center p-4"
             @click.self="showResetModal = false">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xs p-6 text-center"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100">
                <p class="font-bold text-gray-800 mb-2">Start a new conversation?</p>
                <p class="text-sm text-gray-500 mb-5">Your current chat will be saved to history.</p>
                <div class="flex gap-3">
                    <button @click="showResetModal = false" class="flex-1 py-2.5 text-sm border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">Cancel</button>
                    <button @click="doReset()" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition-all hover:opacity-90"
                            style="background: linear-gradient(135deg, #250001, #590004)">Reset</button>
                </div>
            </div>
        </div>

        {{-- Float button --}}
        <button @click="open = !open; if(open && !started) $nextTick(() => $refs.nameInput && $refs.nameInput.focus())"
                class="w-14 h-14 rounded-full text-white shadow-xl flex items-center justify-center relative
                       hover:scale-110 active:scale-95 focus:outline-none focus:ring-4 focus:ring-[#590004]/30"
                style="background: linear-gradient(135deg, #250001, #590004); box-shadow: 0 4px 20px rgba(89,0,4,0.45);
                       transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);"
                :aria-label="open ? 'Close chat' : 'Open policy assistant'">
            <span x-show="!open" style="transition: opacity 0.2s">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </span>
            <span x-show="open" style="transition: opacity 0.2s">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </span>
            {{-- Unread dot --}}
            <span x-show="!open && unread > 0"
                  class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full text-white text-[10px] font-black flex items-center justify-center"
                  x-text="unread"></span>
        </button>
    </div>

    <style>
        .line-clamp-1 { overflow:hidden; display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; }
        .no-scrollbar::-webkit-scrollbar { display:none; }
        .no-scrollbar { -ms-overflow-style:none; scrollbar-width:none; }
        [x-cloak] { display:none !important; }
        .offense-badge {
            display: inline-block;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            padding: 1px 6px;
            border-radius: 9999px;
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .sanction-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            margin: 0.25rem 0;
            font-size: 0.78rem;
            color: #374151;
        }
        .osa-btn {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.35rem 0.9rem;
            background: #590004;
            color: #fff;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: opacity 0.2s;
        }
        .osa-btn:hover { opacity: 0.85; }
    </style>

    <script>
    const __chatRoute = @json(route('student.chat'));

    // â”€â”€ Main page filter/search â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function conductManual() {
        const offenses = window.__offenseData || [];
        return {
            offenses,
            search: '',
            activeCategory: 'all',
            activeSeverity: 'all',

            get filteredOffenses() {
                const q = this.search.toLowerCase().trim();
                return this.offenses.filter(o => {
                    const catOk  = this.activeCategory === 'all' || o.category === this.activeCategory;
                    const sevOk  = this.activeSeverity === 'all'  || o.severity_level === this.activeSeverity;
                    const textOk = !q ||
                        o.code.toLowerCase().includes(q) ||
                        o.title.toLowerCase().includes(q) ||
                        (o.description && o.description.toLowerCase().includes(q)) ||
                        o.category.toLowerCase().includes(q);
                    return catOk && sevOk && textOk;
                });
            },

            filterCategory(cat) { this.activeCategory = cat; },
            filterSeverity(sev) { this.activeSeverity = sev; },
            resetFilters() {
                this.search = '';
                this.activeCategory = 'all';
                this.activeSeverity = 'all';
            },
        };
    }

    // â”€â”€ AI Chatbot â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function chatbot() {
        return {
            open: false,
            started: false,
            typing: false,
            unread: 0,
            activeTab: 'chat',
            lang: 'en',
            studentName: '',
            userInput: '',
            messages: [],            // { role: 'user'|'assistant', content: string }
            chatHistory: [],         // localStorage entries
            showOsaModal: false,
            showResetModal: false,

            get quickPrompts() {
                return this.lang === 'fil'
                    ? ['Ano ang panloloko?', 'Menor na paglabag', 'Mga kaso ng SDT', 'Parusa sa plagiarism?', 'Mga paglabag sa akademya', 'Ano ang major offenses?']
                    : ['What is cheating?', 'List minor offenses', 'What triggers an SDT?', 'Sanction for plagiarism?', 'Academic violations', 'What are major offenses?'];
            },

            init() {
                this.chatHistory = this.loadHistoryFromStorage();
                // Expose OSA modal trigger for use inside x-html rendered buttons
                window._chatbotShowOsa = () => { this.showOsaModal = true; };
                // Show unread dot after 2s to draw attention
                setTimeout(() => { if (!this.open) { this.unread = 1; } }, 2000);
            },

            startChat() {
                if (!this.studentName.trim()) { return; }
                this.started = true;
                this.activeTab = 'chat';
                const greeting = this.lang === 'fil'
                    ? `<strong>Kamusta, ${this.escapeHtml(this.studentName)}! </strong><br>Ako ang iyong AI Assistant para sa Student Conduct Manual ng CSU. Maaari kang magtanong tungkol sa alinman sa <strong>58 na paglabag</strong> sa aming talaan.<br><br>Subukan: <em>"Ano ang parusa sa panloloko?"</em> o <em>"Ano ang mga menor na paglabag?"</em>`
                    : `<strong>Hello, ${this.escapeHtml(this.studentName)}! </strong><br>I'm your AI assistant for the CSU Student Conduct Manual. I can answer questions about any of the <strong>58 offense rules</strong> in detail.<br><br>Try asking: <em>"What is the sanction for cheating?"</em> or <em>"What are major offenses?"</em>`;
                // Mark as synthetic so it is not sent to the AI API as context
                this.messages = [{ role: 'assistant', content: greeting, synthetic: true }];
            },

            async sendMessage(text) {
                if (!text || !text.trim() || this.typing) { return; }
                const message = text.trim();
                this.userInput = '';
                this.unread = 0;
                this.typing = true;
                this.messages.push({ role: 'user', content: message });
                this.$nextTick(() => this.scrollToBottom());

                // Build messages array for API — skip synthetic messages (e.g. welcome greeting)
                // and strip any HTML from assistant replies before sending
                const apiMessages = this.messages
                    .filter(m => !m.synthetic)
                    .map(m => ({
                        role: m.role,
                        content: m.role === 'assistant' ? this.stripHtml(m.content) : m.content,
                    }));

                const historyForApi = apiMessages.slice(-20); // last 20 turns max

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    const response = await fetch(__chatRoute, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                        },
                        body: JSON.stringify({
                            messages: historyForApi,
                            lang: this.lang,
                            studentName: this.studentName,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok || data.error) {
                        const errMsg = data.error || (this.lang === 'fil'
                            ? 'May naganap na error. Subukang muli.'
                            : 'An error occurred. Please try again.');
                        this.messages.push({ role: 'assistant', content: errMsg });
                    } else {
                        this.messages.push({ role: 'assistant', content: data.reply });
                        this.saveSessionToHistory(message, data.reply);
                    }
                } catch (e) {
                    const errMsg = this.lang === 'fil'
                        ? 'Hindi makakonekta sa server. Subukang muli.'
                        : 'Could not connect to the server. Please try again.';
                    this.messages.push({ role: 'assistant', content: errMsg });
                }

                this.typing = false;
                this.$nextTick(() => this.scrollToBottom());
            },

            formatReply(text) {
                let html = text;

                // 1. Bold (**text**)
                html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

                // 2. Section headings (## heading)
                html = html.replace(/^## (.+)$/gm,
                    '<div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#6b7280;border-bottom:1px solid #e5e7eb;padding-bottom:2px;margin:10px 0 4px">$1</div>');

                // 3. Offense code badges (MC-01, AV-03, SA-02, etc.) — after bold so they aren't re-processed
                html = html.replace(/\b([A-Z]{1,4}-\d{2,3})\b/g, '<span class="offense-badge">$1</span>');

                // 4. Sanction tier lines (1st/2nd/3rd Offense:)
                html = html.replace(/^(1st|2nd|3rd|First|Second|Third) Offense:?\s*(.+)$/gim,
                    '<div class="sanction-box"><span style="font-size:0.7rem;font-weight:700;text-transform:uppercase;color:#6b7280">$1 Offense</span><br>$2</div>');

                // 5. Numbered list items (1. 2. 3.)
                html = html.replace(/^(\d+)\. (.+)$/gm,
                    '<div style="display:flex;gap:6px;margin:2px 0;align-items:start"><span style="font-weight:700;color:#590004;flex-shrink:0;font-size:0.75rem;margin-top:1px">$1.</span><span>$2</span></div>');

                // 6. Bullet list items (- item)
                html = html.replace(/^- (.+)$/gm,
                    '<div style="display:flex;gap:6px;margin:2px 0;align-items:start"><span style="font-weight:700;color:#590004;flex-shrink:0;font-size:0.75rem;margin-top:1px">•</span><span>$1</span></div>');

                // 7. OSA contact button
                if (/\bOSA\b|osa@csu\.edu\.ph|Office of Student Affairs/i.test(html)) {
                    html += '<br><button class="osa-btn" onclick="window._chatbotShowOsa && window._chatbotShowOsa()">Contact OSA &#8594;</button>';
                }

                // 8. Paragraph breaks (double newline → spacing)
                html = html.replace(/\n\n+/g, '<br><br>');

                // 9. Remaining single newlines
                html = html.replace(/\n/g, '<br>');

                return html;
            },

            confirmReset() {
                if (this.messages.length > 1) {
                    this.showResetModal = true;
                } else {
                    this.doReset();
                }
            },

            doReset() {
                this.showResetModal = false;
                this.started = false;
                this.messages = [];
                this.userInput = '';
                this.activeTab = 'chat';
            },

            saveSessionToHistory(userMsg, botReply) {
                const entry = {
                    preview: userMsg.length > 60 ? userMsg.substring(0, 60) + '...' : userMsg,
                    date: new Date().toLocaleDateString('en-PH', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }),
                    userMsg,
                    botReply: this.stripHtml(botReply).substring(0, 200),
                };
                this.chatHistory.unshift(entry);
                if (this.chatHistory.length > 30) { this.chatHistory = this.chatHistory.slice(0, 30); }
                this.persistHistory();
            },

            loadHistory(entry) {
                this.activeTab = 'chat';
                const note = this.lang === 'fil'
                    ? `<em style="color:#6b7280;font-size:0.75rem">Mula sa kasaysayan (${entry.date}):</em><br><br>`
                    : `<em style="color:#6b7280;font-size:0.75rem">From history (${entry.date}):</em><br><br>`;
                this.messages.push({ role: 'user', content: entry.userMsg });
                this.messages.push({ role: 'assistant', content: note + entry.botReply });
                this.$nextTick(() => this.scrollToBottom());
            },

            clearHistory() {
                this.chatHistory = [];
                this.persistHistory();
            },

            loadHistoryFromStorage() {
                try {
                    const raw = localStorage.getItem('csu_chatbot_history');
                    return raw ? JSON.parse(raw) : [];
                } catch { return []; }
            },

            persistHistory() {
                try {
                    localStorage.setItem('csu_chatbot_history', JSON.stringify(this.chatHistory));
                } catch {}
            },

            stripHtml(html) {
                const div = document.createElement('div');
                div.innerHTML = html;
                return div.textContent || div.innerText || '';
            },

            escapeHtml(text) {
                return String(text)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            },

            scrollToBottom() {
                const el = this.$refs.messageContainer;
                if (el) { el.scrollTop = el.scrollHeight; }
            },
        };
    }
    </script>
</x-student-app-layout>
