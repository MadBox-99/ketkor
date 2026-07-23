<div>
    <x-slot name="header">
        <x-button-style-link text="Create tool" route="tools.index">
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
                                {{ __('Create tool, datas.') }}
                            </p>
                        </header>
                        <form class="mt-6 space-y-6" wire:submit="save">
                            <div class="mb-4">
                                <x-create-input-text name="name" headText="Tool name"
                                    wire:model="name"></x-create-input-text>
                                <x-select-input name="category" headText="Category" wire:model="category">
                                    @foreach (\App\Enums\ProductCategory::cases() as $productCategory)
                                        <x-select-input-option :value="$productCategory->value">
                                            {{ $productCategory->getLabel() }}
                                        </x-select-input-option>
                                    @endforeach
                                </x-select-input>
                                <x-create-input-text name="tag" headText="tag" wire:model="tag"></x-create-input-text>
                                <x-select-input name="factory_name" headText="Factory name" wire:model="factory_name">
                                    <x-select-input-option value="SIME">
                                        {{ __('SIME') }}
                                    </x-select-input-option>
                                    <x-select-input-option value="Ferroli">
                                        {{ __('Ferroli') }}
                                    </x-select-input-option>
                                </x-select-input>
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
