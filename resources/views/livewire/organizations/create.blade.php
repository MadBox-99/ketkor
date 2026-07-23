<div>
    <x-slot name="header">
        <x-button-style-link text="Create organization" route="organizations.index">
            Back
        </x-button-style-link>
    </x-slot>
    <x-alert />
    <div class="card mb-4 shadow">
        <div class="flex min-h-screen items-center justify-center">
            <div class="w-full max-w-xs">
                <form class="mb-4 rounded bg-white px-8 pb-8 pt-6 shadow-md dark:bg-gray-800" wire:submit="save">
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
            </div>
        </div>
    </div>
</div>
