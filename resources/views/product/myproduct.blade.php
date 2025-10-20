<x-layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ __('My products') }}
            </h2>
        </div>
    </x-slot>

    <x-alert />

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <livewire:product-search-user />
        </div>
    </div>
</x-layouts.app>
