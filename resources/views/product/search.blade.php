<x-app-layout>
    <x-slot name="header">
        <div class="mb-4 flex items-center justify-between font-bold">
            <div class="flex-auto">
                <h1 class="mx-1 px-2 text-3xl text-primary sm:text-2xl md:text-xl">{{ __('Search product') }}</h1>
            </div>
        </div>
    </x-slot>
    <x-alert />
    <div class="my-8 w-full overflow-hidden shadow-sm">
        @livewire('product-search')
    </div>

</x-app-layout>
