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
                <form class="mb-4 rounded bg-white px-8 pb-8 pt-6 shadow-md" wire:submit="save">
                    <div class="mb-4">
                        <x-create-input-text name="name" headText="Organization name"
                            wire:model="name"></x-create-input-text>
                        <x-create-input-text name="city" headText="City" wire:model="city"></x-create-input-text>
                        <x-create-input-text name="address" headText="Address"
                            wire:model="address"></x-create-input-text>
                        <x-create-input-text name="tax_number" headText="Tax number"
                            wire:model="tax_number"></x-create-input-text>
                        <x-create-input-text name="zip" headText="Zip" wire:model="zip"></x-create-input-text>
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
