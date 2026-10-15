<div>
    <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 h-64 overflow-y-auto">
        @if ($ocrText !== '')
            <p class="text-gray-700 text-sm whitespace-pre-wrap font-mono">{{ $ocrText }}</p>
        @elseif ($ocrError)
            <div class="flex h-full flex-col items-center justify-center gap-3 text-center text-red-700">
                <p class="text-sm">{{ $ocrError }}</p>
                @if ($hasDocument)
                    <button
                        type="button"
                        wire:click="retryOcr"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-mahogany px-4 py-2 text-sm font-medium text-white hover:bg-black-cherry disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="retryOcr">Retry / Run OCR</span>
                        <span wire:loading wire:target="retryOcr">Running OCR...</span>
                    </button>
                @endif
            </div>
        @elseif ($hasDocument)
            <div class="flex h-full flex-col items-center justify-center gap-2 text-gray-500">
                <span wire:loading wire:target="mount">Extracting text from attachment...</span>
                <span wire:loading.remove wire:target="mount">OCR text is not available yet.</span>
            </div>
        @else
            <div class="flex h-full items-center justify-center text-sm text-gray-400">
                No OCR text or case attachment is available.
            </div>
        @endif
    </div>
</div>
