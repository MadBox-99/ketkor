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
                        <label for="serial_number"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Serial number') }}
                        </label>
                        <div class="flex gap-3">
                            <div class="relative flex-1">
                                <input type="text" id="serial_number" wire:model="serial_number"
                                    placeholder="{{ __('Enter serial number...') }}"
                                    class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-500 dark:focus:ring-blue-500" />
                                @error('serial_number')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit"
                                class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-gray-800"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove class="flex items-center gap-3">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                    </svg>
                                    <span class="text-base">{{ __('find') }}</span>
                                </span>
                                <span wire:loading class="flex items-center gap-3">
                                    <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span class="text-base">{{ __('Searching...') }}</span>
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div wire:loading class="text-center">
                        <div
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-4 py-2 text-sm font-medium text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ __('Searching product...') }}
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Product Result -->
        @if ($product !== null && $product->exists())
            <div wire:transition class="overflow-hidden rounded-lg bg-white shadow-lg dark:bg-gray-800">
                <div
                    class="border-l-4 border-blue-500 bg-linear-to-r from-blue-50 to-transparent p-6 dark:from-blue-900/20">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">
                                {{ __('Product found') }}
                            </h2>

                            <dl class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-lg bg-white p-4 dark:bg-gray-700/50">
                                    <dt class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ __('Installation location') }}
                                    </dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $product->zip }}, {{ $product->city }}
                                    </dd>
                                    <dd class="text-gray-600 dark:text-gray-300">
                                        {{ $product->street }}
                                    </dd>
                                </div>

                                @if ($product->tool)
                                    <div class="rounded-lg bg-white p-4 dark:bg-gray-700/50">
                                        <dt class="mb-1 text-sm font-medium text-gray-500 dark:text-gray-400">
                                            {{ __('Product type') }}
                                        </dt>
                                        <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ $product->tool->category }}
                                        </dd>
                                    </div>
                                @endif

                                @if ($product->purchase_date)
                                    <div
                                        class="rounded-lg border-2 border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                                        <dt
                                            class="mb-1 flex items-center gap-2 text-sm font-bold text-amber-700 dark:text-amber-400">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            {{ __('Purchase date') }}
                                        </dt>
                                        <dd class="text-lg font-bold text-amber-900 dark:text-amber-300">
                                            {{ $product->purchase_date->format('Y-m-d') }}
                                        </dd>
                                    </div>
                                @endif

                                @if ($product->installation_date)
                                    <div
                                        class="rounded-lg border-2 border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                                        <dt
                                            class="mb-1 flex items-center gap-2 text-sm font-bold text-amber-700 dark:text-amber-400">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            {{ __('Installation date') }}
                                        </dt>
                                        <dd class="text-lg font-bold text-amber-900 dark:text-amber-300">
                                            {{ $product->installation_date->format('Y-m-d') }}
                                        </dd>
                                    </div>
                                @endif

                                @if ($product->tool)
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

                        @if (!$owns)
                            <a href="{{ route('products.add', ['product' => $product->id]) }}" wire:navigate
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-offset-gray-800">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('add to me') }}
                            </a>
                        @else
                            <a href="{{ route('products.edit', ['product' => $product->id]) }}" wire:navigate
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-gray-800">
                                <x-svg.open-eye />
                                {{ __('Open') }}
                            </a>
                            <span
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-4 py-3 text-sm font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                                <x-svg.check />
                                {{ __('You own this product') }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Event Timeline -->
                @if ($product->product_logs->isNotEmpty() || $product->purchase_date || $product->installation_date)
                    <div class="border-t border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-white">
                            <x-svg.clock />
                            {{ __('Event history') }}
                        </h3>
                        <div class="relative space-y-4">
                            <div
                                class="absolute left-4 top-0 h-full w-0.5 bg-linear-to-b from-gray-300 to-transparent dark:from-gray-600">
                            </div>
                            @php
                                $allEvents = collect();

                                if ($product->purchase_date) {
                                    $allEvents->push([
                                        'date' => $product->purchase_date->format('Y-m-d'),
                                        'type' => 'purchase',
                                        'label' => __('Purchase date'),
                                        'icon' => 'shopping-cart',
                                        'important' => true,
                                    ]);
                                }

                                if ($product->installation_date) {
                                    $allEvents->push([
                                        'date' => $product->installation_date->format('Y-m-d'),
                                        'type' => 'installation',
                                        'label' => __('Installation date'),
                                        'icon' => 'wrench',
                                        'important' => true,
                                    ]);
                                }

                                foreach ($product->product_logs as $log) {
                                    $date = $log->when ?? $log->created_at;
                                    $allEvents->push([
                                        'date' => $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : $date,
                                        'type' => 'log',
                                        'label' => $log->what,
                                        'comment' => $log->comment,
                                        'icon' => 'clipboard',
                                        'important' => false,
                                    ]);
                                }

                                $allEvents = $allEvents->sortByDesc('date');
                            @endphp

                            @foreach ($allEvents as $event)
                                <div class="relative flex items-center gap-4 pl-4">
                                    <!-- Timeline dot -->
                                    <div class="relative z-10 shrink-0">
                                        @if ($event['important'])
                                            <div
                                                class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-500 ring-4 ring-white dark:ring-gray-800">
                                                <svg class="h-4 w-4 text-white" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    @if ($event['icon'] === 'shopping-cart')
                                                        <x-svg.shopping-cart />
                                                    @else
                                                        <x-svg.dot />
                                                    @endif
                                                </svg>
                                            </div>
                                        @else
                                            <x-svg.triangle />
                                        @endif
                                    </div>

                                    <!-- Event content -->
                                    <div
                                        class="flex-1 rounded-lg pb-8 p-4 {{ $event['important'] ? 'border-2 border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <p
                                                    class="font-semibold {{ $event['important'] ? 'text-amber-900 dark:text-amber-300' : 'text-gray-900 dark:text-white' }}">
                                                    {{ $event['label'] }}
                                                </p>
                                                @if (isset($event['comment']) && $event['comment'])
                                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                        {{ $event['comment'] }}
                                                    </p>
                                                @endif
                                            </div>
                                            <time
                                                class="shrink-0 text-sm font-medium {{ $event['important'] ? 'text-amber-700 dark:text-amber-400' : 'text-gray-500 dark:text-gray-400' }}">
                                                {{ $event['date'] }}
                                            </time>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @elseif (!$product && $this->serial_number !== '')
            <div
                class="rounded-lg border-2 border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-600 dark:bg-gray-800">
                <x-svg.sad-mile />
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
