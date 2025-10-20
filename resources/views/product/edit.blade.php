<x-layouts.app>
    <!-- Page Header -->
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('Edit product') }}
            </h1>

            <x-button-style-link text="Edit product" route="products.myproducts">
                {{ __('Back') }}
            </x-button-style-link>

        </div>
    </x-slot>

    <!-- Alert Messages -->
    <x-alert />

    <!-- Livewire Component -->
    <livewire:product-edit :product="$product" :userVisibility="$userVisibility" />
</x-layouts.app>
