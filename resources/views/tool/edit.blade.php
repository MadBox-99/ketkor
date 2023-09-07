<x-app-layout>
    <div class="">

        <!-- Page Heading -->
        <x-slot name="header">
            <x-button-style-link text="Edit tool" route="tools.index">
                Back
            </x-button-style-link>
        </x-slot>
        {{-- Alert Messages --}}
        <x-alert />
        <div class="card mb-4 shadow">
            <div class="flex min-h-screen items-center justify-center">
                <div class="w-full max-w-xs">
                    <form method="POST" action="{{ route('tools.update', ['tool' => $tool->id]) }}"
                        class="mb-4 rounded bg-white px-8 pb-8 pt-6 shadow-md">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <x-create-input-text name="name"
                                headText="Tool name">{{ $tool->name }}</x-create-input-text>
                            <x-create-input-text name="category"
                                headText="Category">{{ $tool->category }}</x-create-input-text>
                            <x-create-input-text name="tag" headText="tag">{{ $tool->tag }}</x-create-input-text>
                            <x-create-input-text name="factory_name"
                                headText="Factory name">{{ $tool->factory_name }}</x-create-input-text>
                        </div>
                        {{-- Save Button --}}
                        <button type="submit"
                            class="rounded bg-blue-500 px-4 py-2 text-center font-bold text-white hover:bg-blue-700 focus:outline-none sm:inline-block">
                            {{ __('Save') }}
                        </button>

                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
