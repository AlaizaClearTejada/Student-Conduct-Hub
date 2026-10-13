<div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
    <button @click="open = !open" class="w-full px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between hover:bg-gray-100 transition-colors focus:outline-none">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h3 class="text-sm font-bold text-gray-900">Workflow & Pipeline FAQs (Reference)</h3>
        </div>
        <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </button>
    <div x-show="open" x-transition x-cloak class="p-6 space-y-6">
        <!-- FAQ 1 -->
        <div>
            <h4 class="text-sm font-semibold text-gray-900 mb-1 flex items-center gap-2">
                <span class="text-indigo-600 font-bold">Q:</span> What is the normal Case Pipeline?
            </h4>
            <p class="text-sm text-gray-600 ml-6">
                <strong>Pending Review</strong> (New report accepted) &rarr; 
                <strong>Notice Sent</strong> (Admin emails student) &rarr; 
                <strong>Under Investigation</strong> (Awaiting decision/tribunal) &rarr; 
                <strong>Sanction Active / Resolved</strong> (Sanction applied).
            </p>
        </div>

        <!-- FAQ 2 -->
        <div>
            <h4 class="text-sm font-semibold text-gray-900 mb-1 flex items-center gap-2">
                <span class="text-indigo-600 font-bold">Q:</span> What happens when a case is handled by the Tribunal?
            </h4>
            <p class="text-sm text-gray-600 ml-6">
                OSDW administrators investigate cases in the Tribunal Module. Once the hearing is complete, they update the case status, which synchronizes the Admin Case tracker so the administrator can apply the official sanction.
            </p>
        </div>

        <!-- FAQ 3 -->
        <div>
            <h4 class="text-sm font-semibold text-gray-900 mb-1 flex items-center gap-2">
                <span class="text-indigo-600 font-bold">Q:</span> Why don't I see the "Apply Sanction" button?
            </h4>
            <p class="text-sm text-gray-600 ml-6">
                The "Apply Sanction" button only appears when the case reaches the <strong>Under Investigation</strong> stage. If a case is still in "Pending Review" or "Notice Sent", you must complete those steps first (or wait for the Tribunal to finish).
            </p>
        </div>
    </div>
</div>
