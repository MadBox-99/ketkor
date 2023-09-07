<x-app-layout>
    <x-slot name="header">

        <x-button-style-link text="Products" route="products.search">Find product</x-button-style-link>
    </x-slot>
    <x-alert />

    <div class="my-8 w-full overflow-hidden shadow-sm">
        @livewire('product-search')
    </div>

</x-app-layout>
