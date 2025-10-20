<x-layouts.app>
    <!-- Page Header -->
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('Edit product') }}
            </h1>
            @role('Admin|Operator')
                <x-button-style-link text="Edit product" route="products.index">
                    {{ __('Back') }}
                </x-button-style-link>
            @else
                <x-button-style-link text="Edit product" route="products.myproducts">
                    {{ __('Back') }}
                </x-button-style-link>
            @endrole
        </div>
    </x-slot>

    <!-- Alert Messages -->
    <x-alert />

    <!-- Livewire Component -->
    <livewire:product-edit :product="$product" />
</x-layouts.app>
