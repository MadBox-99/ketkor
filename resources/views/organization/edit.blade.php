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
                            <x-create-input-text name="city" :required="false"
                                headText="City">{{ $organization->city }}</x-create-input-text>
                            <x-create-input-text name="address" :required="false"
                                headText="Address">{{ $organization->address }}</x-create-input-text>
                            <x-create-input-text name="tax_number" :required="false"
                                headText="Tax number">{{ $organization->tax_number }}</x-create-input-text>
                            <x-create-input-text name="zip" :required="false"
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
                    <div class="mb-4 basis-full rounded bg-white px-8 pb-8 pt-6 shadow-md">
                        <div class="flex flex-wrap">
                            <div class="basis-full text-center">
                                <div class="flex flex-nowrap">
                                    <div class="basis-2/12 rounded">
                                        {{ __('Owner name') }}
                                    </div>
                                    <div class="basis-2/12 rounded">
                                        {{ __('Serial number') }}
                                    </div>
                                    <div class="basis-2/12 rounded">
                                        {{ __('City') }}
                                    </div>
                                    <div class="basis-2/12 rounded">
                                        {{ __('Purchase place') }}
                                    </div>
                                    <div class="basis-2/12 rounded">
                                        {{ __('Tool name') }}
                                    </div>
                                    <div class="basis-2/12 rounded">
                                        {{ __('Warrantee date') }}
                                    </div>
                                </div>
                            </div>
                            <div class="basis-full text-center">
                                @foreach ($products as $product)
                                    <div
                                        class="m-auto my-1 flex flex-nowrap self-auto py-1 text-center odd:bg-gray-400">
                                        <div class="basis-2/12 rounded">
                                            {{ $product->owner_name }}
                                        </div>
                                        <div class="basis-2/12 rounded">
                                            {{ $product->serial_number }}
                                        </div>
                                        <div class="basis-2/12 rounded">
                                            {{ $product->city }}
                                        </div>
                                        <div class="basis-2/12 rounded">
                                            {{ $product->purchase_place }}
                                        </div>
                                        <div class="basis-2/12 rounded">
                                            {{ $product->tool->name }}
                                        </div>
                                        <div class="basis-2/12 rounded">
                                            {{ $product->serializeDate($product->warrantee_date) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
