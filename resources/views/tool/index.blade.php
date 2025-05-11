<x-layouts.app>
    <x-slot name="header">
        <x-button-style-link text="Tools" route="tools.create">New tool create</x-button-style-link>
    </x-slot>
    <x-alert />
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                @livewire('tools-table')
            </div>
        </div>
    </div>
</x-layouts.app>
