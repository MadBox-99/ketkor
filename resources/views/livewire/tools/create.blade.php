<div>
    <x-slot name="header">
        <x-button-style-link text="Create tool" route="tools.index">
            Back
        </x-button-style-link>
    </x-slot>

    <x-alert />
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Tool Information') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Create tool, datas.') }}
                            </p>
                        </header>
                        <form class="mt-6 space-y-6" wire:submit="save">
                            <div class="mb-4">
                                {{ $this->form }}
                            </div>
                            {{-- Save Button --}}
                            <button
                                class="rounded bg-blue-500 px-4 py-2 text-center font-bold text-white hover:bg-blue-700 focus:outline-none sm:inline-block"
                                type="submit">
                                {{ __('Save') }}
                            </button>

                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
