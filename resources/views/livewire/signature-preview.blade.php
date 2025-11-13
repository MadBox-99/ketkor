<div class="p-4">
    @if($signature)
        <div class="flex justify-center items-center rounded-lg border-2 border-gray-300 bg-white p-4 dark:border-gray-600 dark:bg-gray-800">
            <img src="{{ $signature }}" alt="Signature" class="max-w-full" style="max-height: 300px;">
        </div>
    @else
        <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-8 dark:border-gray-600 dark:bg-gray-800">
            <svg class="mb-4 h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            <p class="text-gray-600 dark:text-gray-400">{{ __('No signature available') }}</p>
        </div>
    @endif
</div>
