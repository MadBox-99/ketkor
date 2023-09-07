<x-app-layout>
    <x-slot name="header">
        <x-button-style-link text="Products" route="products.create"> New products create</x-button-style-link>
    </x-slot>
    <x-alert />
    <div class="mx-20 flex max-w-full flex-row justify-center px-20 py-2">
        <div class="relative overflow-auto rounded-xl">
            <div class="my-8 w-full overflow-hidden shadow-sm">
                @livewire('search-product')
            </div>
        </div>
    </div>
</x-app-layout>
