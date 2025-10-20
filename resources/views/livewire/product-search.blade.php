<div class="min-h-screen bg-gray-50 py-12 dark:bg-gray-900">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
                {{ __('Product search') }}
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ __('Search for products by serial number') }}
            </p>
        </div>

        <!-- Search Form -->
        <form wire:submit="find" class="mb-8">
            <div class="rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
                <div class="space-y-4">
                    <!-- Search Input Group -->
                    <div>
                        <label for="serial_number" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Serial number') }}
                        </label>
                        <div class="flex gap-3">
                            <div class="relative flex-1">
                                <input
                                    type="text"
                                    id="serial_number"
                                    wire:model="serial_number"
                                    placeholder="{{ __('Enter serial number...') }}"
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                />
                                @error('serial_number')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <button
                                type="submit"
                                class="flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-gray-800"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove>
                                    <x-search-icon />
                                </span>
                                <span wire:loading>
                                    <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div wire:loading class="text-center">
                        <div class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-4 py-2 text-sm font-medium text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('Searching product...') }}
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Product Result -->
        @if ($product !== null && $product->exists())
            <div class="overflow-hidden rounded-lg bg-white shadow-lg dark:bg-gray-800" wire:transition>
                <div class="border-l-4 border-blue-500 bg-gradient-to-r from-blue-50 to-transparent p-6 dark:from-blue-900/20">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">
                                {{ __('Product found') }}
                            </h2>

                            <dl class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-lg bg-white p-4 dark:bg-gray-700/50">
                                    <dt class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('Location') }}
                                    </dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $product->zip }} {{ $product->city }}
                                    </dd>
                                    <dd class="text-gray-600 dark:text-gray-300">
                                        {{ $product->street }}
                                    </dd>
                                </div>

                                <div class="rounded-lg bg-white p-4 dark:bg-gray-700/50">
                                    <dt class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('Warranty date') }}
                                    </dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $product->serializeDate($product->warrantee_date) }}
                                    </dd>
                                </div>

                                @if($product->tool)
                                    <div class="rounded-lg bg-white p-4 dark:bg-gray-700/50">
                                        <dt class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                                            {{ __('Tool name') }}
                                        </dt>
                                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ $product->tool->name }}
                                        </dd>
                                    </div>
                                @endif

                                <div class="rounded-lg bg-white p-4 dark:bg-gray-700/50">
                                    <dt class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('Serial number') }}
                                    </dt>
                                    <dd class="text-lg font-mono font-semibold text-gray-900 dark:text-white">
                                        {{ $product->serial_number }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        @if ($owns < 1)
                            <a
                                href="{{ route('products.add', ['product' => $product->id]) }}"
                                wire:navigate
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-offset-gray-800"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('add to me') }}
                            </a>
                        @else
                            <a
                                href="{{ route('products.edit', ['product' => $product->id]) }}"
                                wire:navigate
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-gray-800"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ __('Open') }}
                            </a>
                            <span class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-4 py-3 text-sm font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                {{ __('You own this product') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @elseif ($product !== null && !$product->exists())
            <div class="rounded-lg border-2 border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-600 dark:bg-gray-800">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('No product found') }}
                </h3>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ __('Please check the serial number and try again.') }}
                </p>
            </div>
        @endif
    </div>
</div>
