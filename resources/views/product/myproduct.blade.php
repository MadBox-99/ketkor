<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <div class="">
                <h1 class="mb-0 text-gray-800">{{ __('My products') }}</h1>
            </div>
        </h2>
    </x-slot>
    <x-alert />
    <div class="mx-2 flex max-w-full flex-row justify-center px-2 py-2 text-3xl xl:mx-20 xl:px-20">
        <div class="relative overflow-auto rounded-xl">
            <div class="my-8 w-full overflow-hidden shadow-sm">
                @livewire('product-search-user')
            </div>
        </div>
    </div>
</x-app-layout>
