<div>
    <x-slot name="header">
        <x-button-style-link text="Organization edit" route="organizations.index">Back</x-button-style-link>
    </x-slot>
    {{-- Alert Messages --}}
    <x-alert />
    <div class="space-y-12">
        <div class="border-b flex justify-center border-gray-900/10 pb-12">
            <div class="flex w-full max-w-7xl flex-wrap text-center">
                <form class="mb-4 flex basis-full flex-wrap rounded bg-white px-8 pb-8 pt-6 shadow-md"
                    wire:submit="save">
                    <div class="flex basis-full flex-wrap gap-4 text-left">
                        <x-filament-forms::field-wrapper :label="__('Organization name')" :required="true" id="name" statePath="name" class="basis-full">
                            <x-filament::input.wrapper :valid="! $errors->has('name')">
                                <x-filament::input type="text" id="name" wire:model="name" placeholder="{{ __('Organization name') }}" />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <x-filament-forms::field-wrapper :label="__('City')" :required="false" id="city" statePath="city" class="basis-full">
                            <x-filament::input.wrapper :valid="! $errors->has('city')">
                                <x-filament::input type="text" id="city" wire:model="city" placeholder="{{ __('City') }}" />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <x-filament-forms::field-wrapper :label="__('Address')" :required="false" id="address" statePath="address" class="basis-full">
                            <x-filament::input.wrapper :valid="! $errors->has('address')">
                                <x-filament::input type="text" id="address" wire:model="address" placeholder="{{ __('Address') }}" />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <x-filament-forms::field-wrapper :label="__('Tax number')" :required="false" id="tax_number" statePath="tax_number" class="basis-full">
                            <x-filament::input.wrapper :valid="! $errors->has('tax_number')">
                                <x-filament::input type="text" id="tax_number" wire:model="tax_number" placeholder="{{ __('Tax number') }}" />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>

                        <x-filament-forms::field-wrapper :label="__('zip')" :required="false" id="zip" statePath="zip" class="basis-full">
                            <x-filament::input.wrapper :valid="! $errors->has('zip')">
                                <x-filament::input type="text" id="zip" wire:model="zip" placeholder="{{ __('zip') }}" />
                            </x-filament::input.wrapper>
                        </x-filament-forms::field-wrapper>
                    </div>
                    <div class="basis-full text-left">
                        {{-- Save Button --}}
                        <button
                            class="my-10 rounded bg-blue-500 px-4 py-2 text-center font-bold text-white hover:bg-blue-700 focus:outline-none sm:inline-block"
                            type="submit">
                            {{ __('Save') }}
                        </button>
                    </div>

                </form>

            </div>
        </div>
        <div class="mb-4 basis-full rounded bg-white px-8 pb-8 pt-6 shadow-md">
            <div class="flex flex-wrap">
                <div class="basis-full">
                    <livewire:organizations.users-table :organization="$organization->id" />
                </div>
            </div>
        </div>
    </div>
</div>
