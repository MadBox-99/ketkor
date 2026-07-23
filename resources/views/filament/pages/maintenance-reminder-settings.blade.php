<x-filament::page>
    {{ $this->form }}

    @if ($previewBody !== null)
        <x-filament::section :heading="'Előnézet'">
            <p class="font-semibold">{{ $previewSubject }}</p>
            <p class="mt-4 whitespace-pre-line">{{ $previewBody }}</p>
        </x-filament::section>
    @endif
</x-filament::page>
