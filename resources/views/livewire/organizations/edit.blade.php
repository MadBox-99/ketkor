<div>
    <x-slot name="header">
        <x-button-style-link text="Organization edit" route="organizations.index">Back</x-button-style-link>
    </x-slot>
    {{-- Alert Messages --}}
    <x-alert />
    <div class="space-y-12">
        <div class="border-b flex justify-center border-gray-900/10 pb-12">
            <div class="flex w-full max-w-7xl flex-wrap text-center">
                <form class="mb-4 flex basis-full flex-wrap rounded bg-white px-8 pb-8 pt-6 shadow-md dark:bg-gray-800"
                    wire:submit="save">
                    <div class="basis-full text-left">
                        {{ $this->form }}
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
        <div class="mb-4 basis-full rounded bg-white px-8 pb-8 pt-6 shadow-md dark:bg-gray-800">
            <div class="flex flex-wrap">
                <div class="basis-full">
                    <livewire:organizations.users-table :organization="$organization->id" />
                </div>
            </div>
        </div>
    </div>
</div>
