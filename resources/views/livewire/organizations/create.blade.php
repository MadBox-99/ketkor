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
                    <div class="mb-4 space-y-4">
                        <x-filament-forms::field-wrapper :label="__('Organization name')" :required="true" id="name" statePath="name">
                            <x-filament::input.wrapper :valid="! $errors->has('name')">
                                <x-filament::input type="text" id="name" wire:model="name" placeholder="{{ __('Organization name') }}" />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <x-filament-forms::field-wrapper :label="__('City')" :required="true" id="city" statePath="city">
                            <x-filament::input.wrapper :valid="! $errors->has('city')">
                                <x-filament::input type="text" id="city" wire:model="city" placeholder="{{ __('City') }}" />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <x-filament-forms::field-wrapper :label="__('Address')" :required="true" id="address" statePath="address">
                            <x-filament::input.wrapper :valid="! $errors->has('address')">
                                <x-filament::input type="text" id="address" wire:model="address" placeholder="{{ __('Address') }}" />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <x-filament-forms::field-wrapper :label="__('Tax number')" :required="true" id="tax_number" statePath="tax_number">
                            <x-filament::input.wrapper :valid="! $errors->has('tax_number')">
                                <x-filament::input type="text" id="tax_number" wire:model="tax_number" placeholder="{{ __('Tax number') }}" />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <x-filament-forms::field-wrapper :label="__('Zip')" :required="true" id="zip" statePath="zip">
                            <x-filament::input.wrapper :valid="! $errors->has('zip')">
                                <x-filament::input type="text" id="zip" wire:model="zip" placeholder="{{ __('Zip') }}" />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>
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
