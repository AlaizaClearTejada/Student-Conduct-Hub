<x-admin-layout>
    <x-slot name="title">Review Tribunal Case</x-slot>
    <x-slot name="breadcrumb">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-mahogany">Dashboard</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <a href="{{ route('tribunal.cases.index') }}" class="hover:text-mahogany">Tribunal Cases</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="text-gray-900 font-medium">Review Case</span>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <p class="text-green-800 text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <p class="text-red-800 text-sm font-medium">{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Case Details: {{ $case->case_number ?? 'N/A' }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ $case->title ?? 'Tribunal Document' }}</p>
                    @if($case->violationRecord?->offenseRule)
                        <p class="text-sm text-gray-600 mt-1">Disciplinary Violation: {{ $case->violationRecord->offenseRule->title }}</p>
                    @endif
                </div>
                <div>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold shadow-sm border border-blue-200">
                        Status: {{ $case->status->value ?? $case->status }}
                    </span>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Case Information & OCR Text -->
                <div class="space-y-6">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Description</h4>
                        <p class="text-gray-700 text-sm bg-gray-50 p-4 rounded-lg border border-gray-100">
                            {{ $case->description ?? 'No description provided.' }}
                        </p>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">OCR Extracted Text (Searchable)</h4>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 h-64 overflow-y-auto">
                            @if($case->searchable_text)
                                <p class="text-gray-700 text-sm whitespace-pre-wrap font-mono">{{ $case->searchable_text }}</p>
                            @else
                                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <span class="text-sm">No OCR text available or processing is pending.</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Pipeline State Machine Updates -->
                    @if(isset($case->status) && $case->status instanceof \App\Enums\CaseStatus)
                        <div class="pt-4 border-t border-gray-100">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Update Pipeline Status</h4>
                            @php
                                $validTransitions = $case->status->validTransitions();
                            @endphp
                            
                            @if(count($validTransitions) > 0)
                                <form action="{{ route('tribunal.cases.updateStatus', $case) }}" method="POST" class="flex items-center gap-3">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="block w-full text-sm border-gray-300 rounded-lg focus:ring-mahogany focus:border-mahogany">
                                        <option value="">Select next stage...</option>
                                        @foreach($validTransitions as $transition)
                                            <option value="{{ $transition->value }}">{{ ucwords(str_replace('_', ' ', $transition->value)) }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="bg-mahogany text-white px-4 py-2 text-sm font-medium rounded-lg hover:bg-black-cherry transition-colors whitespace-nowrap">
                                        Transition State
                                    </button>
                                </form>
                            @else
                                <div class="p-3 bg-green-50 text-green-800 text-sm rounded-lg border border-green-200">
                                    This case has reached its final pipeline state ({{ $case->status->value }}).
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Document Viewer -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Original Scanned Document</h4>
                    <div class="bg-gray-100 rounded-lg border border-gray-200 h-[32rem] flex items-center justify-center overflow-hidden">
                        @if($case->document_path)
                            @if(Str::endsWith(strtolower($case->document_path), ['.pdf']))
                                <iframe src="{{ route('tribunal.cases.document', $case) }}" title="Original case document" class="w-full h-full" frameborder="0"></iframe>
                            @elseif(Str::endsWith(strtolower($case->document_path), ['.jpg', '.jpeg', '.png']))
                                <img src="{{ route('tribunal.cases.document', $case) }}" alt="Scanned Document" class="max-w-full max-h-full object-contain">
                            @else
                                <a href="{{ route('tribunal.cases.document', $case) }}" target="_blank" class="text-mahogany hover:underline font-medium flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download Document
                                </a>
                            @endif
                        @else
                            <div class="text-gray-400 flex flex-col items-center">
                                <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-sm font-medium">No document attached.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
