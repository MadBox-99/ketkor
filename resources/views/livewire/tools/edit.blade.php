<div>
    <x-slot name="header">
        <x-button-style-link text="Edit tool" route="tools.index">
            Back
        </x-button-style-link>
    </x-slot>
    <x-alert />
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Tool Information') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Update tool, datas.') }}
                            </p>
                        </header>
                        <form class="mt-6 space-y-6" wire:submit="save">
                            <div class="mb-4 space-y-4">
                                <x-filament-forms::field-wrapper :label="__('Tool name')" :required="true" id="name" statePath="name">
                                    <x-filament::input.wrapper :valid="! $errors->has('name')">
                                        <x-filament::input type="text" id="name" wire:model="name" placeholder="{{ __('Tool name') }}" />
                                    </x-filament::input.wrapper>
                                </x-filament-forms::field-wrapper>

                                <x-filament-forms::field-wrapper :label="__('Category')" :required="true" id="category" statePath="category">
                                    <x-filament::input.wrapper :valid="! $errors->has('category')">
                                        <x-filament::input.select id="category" wire:model="category">
                                            @foreach (\App\Enums\ProductCategory::cases() as $productCategory)
                                                <x-select-input-option :value="$productCategory->value">
                                                    {{ $productCategory->getLabel() }}
                                                </x-select-input-option>
                                            @endforeach
                                        </x-filament::input.select>
                                    </x-filament::input.wrapper>
                                </x-filament-forms::field-wrapper>

                                <x-filament-forms::field-wrapper :label="__('tag')" :required="false" id="tag" statePath="tag">
                                    <x-filament::input.wrapper :valid="! $errors->has('tag')">
                                        <x-filament::input type="text" id="tag" wire:model="tag" placeholder="{{ __('tag') }}" />
                                    </x-filament::input.wrapper>
                                </x-filament-forms::field-wrapper>

                                <x-filament-forms::field-wrapper :label="__('Factory name')" :required="true" id="factory_name" statePath="factory_name">
                                    <x-filament::input.wrapper :valid="! $errors->has('factory_name')">
                                        <x-filament::input.select id="factory_name" wire:model="factory_name">
                                            <x-select-input-option value="SIME">
                                                {{ __('SIME') }}
                                            </x-select-input-option>
                                            <x-select-input-option value="Ferroli">
                                                {{ __('Ferroli') }}
                                            </x-select-input-option>
                                        </x-filament::input.select>
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
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
