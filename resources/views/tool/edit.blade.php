<x-layouts.app>
    <div class="">

        <!-- Page Heading -->
        <x-slot name="header">
            <x-button-style-link text="Edit tool" route="tools.index">
                Back
            </x-button-style-link>
        </x-slot>
        {{-- Alert Messages --}}
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
                            <form class="mt-6 space-y-6" method="POST"
                                action="{{ route('tools.update', ['tool' => $tool->id]) }}">
                                @csrf
                                @method('PUT')
                                <div class="mb-4">
                                    <x-create-input-text name="name"
                                        headText="Tool name">{{ $tool->name }}</x-create-input-text>
                                    <x-select-input name="category" headText="Category" :required="true">
                                        <x-select-input-option value="Boiler" :selected="$tool->category == 'Boiler'">
                                            {{ __('Boiler') }}
                                        </x-select-input-option>
                                        <x-select-input-option value="Heat pump" :selected="$tool->category == 'Heat pump'">
                                            {{ __('Heat pump') }}
                                        </x-select-input-option>
                                    </x-select-input>
                                    <x-create-input-text name="tag" :required="false"
                                        headText="tag">{{ $tool->tag }}</x-create-input-text>
                                    <x-select-input name="factory_name" headText="Factory name">
                                        <x-select-input-option value="SIME" :selected="$tool->factory_name == 'SIME'">
                                            {{ __('SIME') }}
                                        </x-select-input-option>
                                        <x-select-input-option value="Ferroli" :selected="$tool->factory_name == 'Ferroli'">
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
</x-layouts.app>
