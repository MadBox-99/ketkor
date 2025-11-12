<div class="min-h-screen bg-gray-50 py-8 dark:bg-gray-900">

    <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
        @if (!$userVisibility)
            {{ $this->permissionAction }}
        @endif
        <!-- Product Information Form -->
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <form wire:submit="updateProduct">
                {{ $this->productForm }}

                <div class="flex items-center gap-4 border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    @if ($userVisibility)
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-offset-gray-800">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('Save') }}
                        </button>
                    @else
                        <a href="{{ route('accestokens.createAccessToken', ['product' => $product->id]) }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:bg-amber-500 dark:hover:bg-amber-600 dark:focus:ring-offset-gray-800">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            {{ __('Require access') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Product Events Form -->
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <form wire:submit="createEvent">
                {{ $this->eventForm }}

                <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-offset-gray-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Create') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Product History Section -->
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <div
                class="border-l-4 border-purple-500 bg-gradient-to-r from-purple-50 to-transparent p-6 dark:from-purple-900/20">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ __('Product history') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Product history list') }}
                </p>
            </div>

            <div class="overflow-x-auto">
                @if ($product->product_logs->isNotEmpty())
                    <table class="w-full text-left text-sm">
                        <thead
                            class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-6 py-3">{{ __('event') }}</th>
                                <th class="px-6 py-3">{{ __('event content') }}</th>
                                <th class="px-6 py-3">{{ __('Mode') }}</th>
                                <th class="px-6 py-3">{{ __('event time') }}</th>
                                <th class="px-6 py-3">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($product->product_logs as $log)
                                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        {{ __($log->what) }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                        {{ $log->comment }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                        @if ($log->is_online)
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ __('Online') }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ __('Offline') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                        {{ $product->serializeDate($log->when) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ ($this->generateWorksheetAction->arguments(['productLogId' => $log->id]))(['size' => 'sm']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-4 text-gray-600 dark:text-gray-400">
                            {{ __('No product log') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Owner Data Section -->
        @if ($userVisibility)
            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <form wire:submit="updateOwner">
                    {{ $this->ownerForm }}

                    <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-500 dark:hover:bg-indigo-600 dark:focus:ring-offset-gray-800">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            {{ __('Update') }}
                        </button>
                    </div>
                </form>

                <!-- Ownership History -->
                @if ($product->partials->count() > 1)
                    <div class="border-t border-gray-200 p-6 dark:border-gray-700">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('Ownership modifications history') }}
                        </h3>
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('History of ownership data modifications.') }}
                        </p>

                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="w-full text-left text-sm">
                                <thead
                                    class="border-b border-gray-200 bg-indigo-50 text-xs uppercase text-gray-700 dark:border-gray-700 dark:bg-indigo-900/20 dark:text-gray-300">
                                    <tr>
                                        <th class="px-6 py-3">{{ __('name') }}</th>
                                        <th class="px-6 py-3">{{ __('Email') }}</th>
                                        <th class="px-6 py-3">{{ __('Mobile') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($product->partials as $partial)
                                        @if ($loop->first)
                                            @continue
                                        @endif
                                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                                {{ $partial->name }}
                                            </td>
                                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                                {{ $partial->email }}
                                            </td>
                                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                                {{ $partial->phone }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
    <x-filament-actions::modals />
</div>
