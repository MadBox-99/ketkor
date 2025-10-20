<div class="space-y-6">
    <!-- Search Filters -->
    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
            {{ __('Search & Filter') }}
        </h3>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Serial Number -->
            <div>
                <label for="serial_number_search" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Serial number') }}
                </label>
                <input type="text" id="serial_number_search" wire:model.live.debounce.500ms="serial_number"
                    placeholder="{{ __('Search by serial...') }}"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500" />
            </div>

            <!-- Tool Name -->
            <div>
                <label for="tool_name_search" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Tool name') }}
                </label>
                <input type="text" id="tool_name_search" wire:model.live.debounce.500ms="tool_name"
                    placeholder="{{ __('Search by tool...') }}"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500" />
            </div>

            <!-- Warranty Start Date -->
            <div>
                <label for="warrantee_date_start"
                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Warranty from') }}
                </label>
                <input type="date" id="warrantee_date_start" wire:model.live.debounce.500ms="warrantee_date_start"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500" />
            </div>

            <!-- Warranty End Date -->
            <div>
                <label for="warrantee_date_end" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Warranty to') }}
                </label>
                <input type="date" id="warrantee_date_end" wire:model.live.debounce.500ms="warrantee_date_end"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500" />
            </div>
        </div>

        <!-- Loading Indicator -->
        <div wire:loading class="mt-4">
            <div class="flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400">
                <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                {{ __('Searching...') }}
            </div>
        </div>
    </div>

    <!-- Products Table/Cards -->
    @if ($products->count() > 0)
        <!-- Desktop Table View -->
        <div class="hidden overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800 lg:block">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="px-6 py-3">{{ __('Serial number') }}</th>
                            <th class="px-6 py-3">{{ __('Owner') }}</th>
                            <th class="px-6 py-3">{{ __('Type') }}</th>
                            <th class="px-6 py-3">{{ __('Tool') }}</th>
                            <th class="px-6 py-3">{{ __('Location') }}</th>
                            <th class="px-6 py-3">{{ __('Warranty') }}</th>
                            <th class="px-6 py-3 text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($products as $product)
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 font-mono text-xs font-medium text-gray-900 dark:text-white">
                                    {{ $product->serial_number }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($product->are_visible[0]->isVisible)
                                        @if (!$product->partials->isEmpty())
                                            <span class="text-gray-700 dark:text-gray-300">
                                                {{ $partials->name }}
                                            </span>
                                        @endif
                                    @else
                                        <a href="{{ route('accestokens.createAccessToken', ['product' => $product->id]) }}"
                                            wire:navigate
                                            class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            {{ __('Request access') }}
                                        </a>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ $product->are_visible[0]->isVisible ? $product->tool->category : '-' }}
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ $product->are_visible[0]->isVisible ? $product->tool->name : '-' }}
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    @if ($product->are_visible[0]->isVisible)
                                        <div class="text-sm">
                                            <div>{{ $product->city }}</div>
                                            <div class="text-gray-500 dark:text-gray-400">{{ $product->street }}</div>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ $product->are_visible[0]->isVisible ? $product->serializeDate($product->warrantee_date) : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('products.edit', ['product' => $product->id]) }}"
                                            wire:navigate
                                            class="rounded-lg p-2 text-blue-600 transition hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20"
                                            title="{{ __('View details') }}">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <form method="POST"
                                            action="{{ route('products.remove', ['product' => $product->id]) }}"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                onclick="return confirm('{{ __('Are you sure you want to remove this product?') }}')"
                                                class="rounded-lg p-2 text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                                title="{{ __('Remove product') }}">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="space-y-4 lg:hidden">
            @foreach ($products as $product)
                <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <div class="mb-3 flex items-start justify-between">
                        <div class="flex-1">
                            <div class="mb-1 font-mono text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $product->serial_number }}
                            </div>
                            @if ($product->are_visible[0]->isVisible)
                                @if (!$product->partials->isEmpty())
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $partials->name }}
                                    </div>
                                @endif
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('products.edit', ['product' => $product->id]) }}" wire:navigate
                                class="rounded-lg p-2 text-blue-600 transition hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('products.remove', ['product' => $product->id]) }}"
                                class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('{{ __('Are you sure?') }}')"
                                    class="rounded-lg p-2 text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    @if ($product->are_visible[0]->isVisible)
                        <dl class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('Tool') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $product->tool->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('Type') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $product->tool->category }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('Location') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">{{ $product->city }},
                                    {{ $product->street }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">{{ __('Warranty') }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">
                                    {{ $product->serializeDate($product->warrantee_date) }}</dd>
                            </div>
                        </dl>
                    @else
                        <a href="{{ route('accestokens.createAccessToken', ['product' => $product->id]) }}"
                            wire:navigate
                            class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            {{ __('Request access to view details') }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="rounded-lg bg-white px-6 py-4 shadow dark:bg-gray-800">
            {{ $products->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div
            class="rounded-lg border-2 border-dashed border-gray-300 bg-white p-12 text-center dark:border-gray-600 dark:bg-gray-800">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('No products found') }}
            </h3>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                {{ __('You don\'t have any products yet or no products match your search criteria.') }}
            </p>
        </div>
    @endif
</div>
