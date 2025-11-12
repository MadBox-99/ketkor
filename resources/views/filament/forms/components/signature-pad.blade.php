@php
    $id = $getId();
    $statePath = $getStatePath();
    $height = $getHeight();
    $isDarkMode = \Illuminate\Support\Facades\Request::cookie('theme') === 'dark' || (! \Illuminate\Support\Facades\Request::cookie('theme') && config('filament.dark_mode'));
    $backgroundColor = $getBackgroundColor() ?? ($isDarkMode ? 'rgb(55, 65, 81)' : 'rgb(255, 255, 255)');
    $penColor = $getPenColor() ?? ($isDarkMode ? 'rgb(255, 255, 255)' : 'rgb(0, 0, 0)');
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="signaturePadFormComponent({
            state: $wire.$entangle(@js($statePath)),
            backgroundColor: @js($backgroundColor),
            penColor: @js($penColor),
        })" x-load-src="@js(\Filament\Support\Facades\FilamentAsset::getScriptSrc('signature-pad-field'))"
        {{ $getExtraAttributeBag() }}>

        <div class="signature-wrapper">
            <canvas x-ref="canvas"
                class="w-full rounded-lg border-2 border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-700"
                style="height: {{ $height }}px; touch-action: none;"></canvas>

            @if ($getShowClearButton())
                <button type="button" @click="clear"
                    class="mt-2 inline-flex items-center gap-2 rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-500 dark:hover:bg-gray-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    {{ $getClearButtonLabel() }}
                </button>
            @endif
        </div>
    </div>
</x-dynamic-component>
