<x-layouts.app>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            <div class="">
                <h1 class="mb-0 text-gray-800">{{ __('My products') }}</h1>
            </div>
        </h2>
    </x-slot>
    <x-alert />
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                @livewire('product-search-user')
            </div>
        </div>
    </div>
</x-layouts.app>
