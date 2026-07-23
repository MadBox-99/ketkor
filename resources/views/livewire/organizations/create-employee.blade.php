<div>
    <x-slot name="header">
        <x-button-style-link text="Add user" route="organizations.myorganization">
            Back
        </x-button-style-link>
    </x-slot>

    {{-- Alert Messages --}}
    <x-alert />
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8 dark:bg-gray-800">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('User create form') }}
                            </h2>
                        </header>
                        <form class="mt-6 space-y-6" wire:submit="save">
                            <div class="mb-4 space-y-4">
                                <x-filament-forms::field-wrapper :label="__('User name')" :required="true" id="name" statePath="name">
                                    <x-filament::input.wrapper :valid="! $errors->has('name')">
                                        <x-filament::input type="text" id="name" wire:model="name" placeholder="{{ __('User name') }}" />
                                    </x-filament::input.wrapper>
                                </x-filament-forms::field-wrapper>

                                <x-filament-forms::field-wrapper :label="__('Email')" :required="true" id="email" statePath="email">
                                    <x-filament::input.wrapper :valid="! $errors->has('email')">
                                        <x-filament::input type="email" id="email" wire:model="email" placeholder="{{ __('Email') }}" />
                                    </x-filament::input.wrapper>
                                </x-filament-forms::field-wrapper>

                                <x-filament-forms::field-wrapper :label="__('Password')" :required="true" id="password" statePath="password">
                                    <x-filament::input.wrapper :valid="! $errors->has('password')">
                                        <x-filament::input type="password" id="password" wire:model="password" placeholder="{{ __('Password') }}" />
                                    </x-filament::input.wrapper>
                                </x-filament-forms::field-wrapper>
                            </div>
                            {{-- Save Button --}}
                            <button
                                class="rounded bg-blue-500 px-4 py-2 text-center font-bold text-white hover:bg-blue-700 focus:outline-none sm:inline-block"
                                type="submit">
                                {{ __('Create') }}
                            </button>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
