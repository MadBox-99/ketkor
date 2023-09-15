<x-app-layout>
    <div class="">

        <!-- Page Heading -->
        <x-slot name="header">
            <x-button-style-link text="Organization edit" route="organizations.index">Back</x-button-style-link>
        </x-slot>
        {{-- Alert Messages --}}
        <x-alert />
        <div class="space-y-12">
            <div class="border-b flex justify-center border-gray-900/10 pb-12">
                <div class="flex w-full max-w-7xl flex-wrap text-center">
                    <form class="mb-4 flex basis-full flex-wrap rounded bg-white px-8 pb-8 pt-6 shadow-md" method="POST"
                        action="{{ route('organizations.update', ['organization' => $organization->id]) }}">
                        @csrf
                        @method('PUT')
                        <div class="flex flex-wrap">
                            <x-create-input-text name="name"
                                headText="Organization name">{{ $organization->name }}</x-create-input-text>
                            <x-create-input-text name="city"
                                headText="City">{{ $organization->city }}</x-create-input-text>
                            <x-create-input-text name="address"
                                headText="Address">{{ $organization->address }}</x-create-input-text>
                            <x-create-input-text name="tax_number"
                                headText="Tax number">{{ $organization->tax_number }}</x-create-input-text>
                            <x-create-input-text name="zip"
                                headText="zip">{{ $organization->zip }}</x-create-input-text>
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
                    <div class="mb-4 rounded bg-white px-8 pb-8 pt-6 shadow-md">
                        <div class="flex flex-wrap">
                            <div class="flex basis-full flex-nowrap text-left">
                                <div class="basis-2/12 rounded shadow-sm">
                                    {{ __('Owner name') }}
                                </div>
                                <div class="basis-2/12 rounded shadow-sm">
                                    {{ __('Serial number') }}
                                </div>
                                <div class="basis-1/12 rounded shadow-sm">
                                    {{ __('City') }}
                                </div>
                                <div class="basis-1/12 rounded shadow-sm">
                                    {{ __('Purchase place') }}
                                </div>
                                <div class="basis-1/12 rounded shadow-sm">
                                    {{ __('Name') }}
                                </div>
                                <div class="basis-1/12 rounded shadow-sm">
                                    {{ __('Warrantee date') }}
                                </div>
                                <div class="basis-1/12 rounded shadow-sm">
                                    {{ __('Actions') }}
                                </div>
                            </div>
                            @foreach ($products as $product)
                                <div class="m-auto my-1 flex basis-full flex-nowrap self-auto py-1 text-left">
                                    <div class="basis-2/12 rounded shadow-sm">
                                        {{ $product->owner_name }}
                                    </div>
                                    <div class="basis-2/12 rounded shadow-sm">
                                        {{ $product->serial_number }}
                                    </div>
                                    <div class="basis-1/12 rounded shadow-sm">
                                        {{ $product->city }}
                                    </div>
                                    <div class="basis-1/12 rounded shadow-sm">
                                        {{ $product->purchase_place }}
                                    </div>
                                    <div class="basis-1/12 rounded shadow-sm">
                                        {{ $product->tool->name }}
                                    </div>
                                    <div class="basis-1/12 rounded shadow-sm">
                                        {{ $product->warrantee_date }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
