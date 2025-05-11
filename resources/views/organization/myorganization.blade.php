<x-layouts.app>
    <!-- Page Heading -->
    <x-slot name="header">
        <x-button-style-link text="Organization edit" route="organizations.createEmployee">
            Create Employee
        </x-button-style-link>
    </x-slot>
    {{-- Alert Messages --}}
    <x-alert />
    {{-- Page content --}}

    <div class="my-12">
        <div class="border-b flex justify-center border-gray-900/10 pb-12">
            <div class="flex w-full max-w-7xl flex-wrap justify-center text-center">
                <div class="w-full" name='form_field'>
                    <form class="mb-4 flex basis-full flex-wrap justify-center rounded bg-white px-8 pb-8 pt-6 shadow-md"
                        method="POST"
                        action="{{ route('organizations.myorganizationupdate', ['organization' => $organization->id]) }}">
                        @csrf
                        @method('PUT')
                        <div class="flex flex-wrap">
                            <div class="basis-full text-left text-xl">
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
                                        headText="Zip">{{ $organization->zip }}</x-create-input-text>
                                </div>
                            </div>
                            <div class="basis-full text-left">
                                {{-- Save Button --}}
                                <button
                                    class="my-10 rounded bg-blue-500 px-4 py-2 text-center font-bold text-white hover:bg-blue-700 focus:outline-none sm:inline-block"
                                    type="submit">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @foreach ($organization->users as $user)
                    <div
                        class="mb-4 flex w-full flex-wrap justify-center rounded bg-white px-8 pb-8 pt-6 text-xl shadow-md">
                        {{-- row 1 --}}
                        <div class="basis-full">
                            <div class="flex h-24 w-full content-between items-center">
                                <div class="basis-2/12">
                                    {{ $user->name }}
                                </div>
                                <div class="basis-7/12">
                                    &nbsp;
                                </div>
                                <div class="basis-3/12 rounded-md bg-red-600 text-center text-white">
                                    <a
                                        href="{{ route('organizations.removeUserFromOrganization', ['user' => $user]) }}">{{ __('user delete') }}</a>
                                </div>
                            </div>
                        </div>
                        {{-- row 2 --}}
                        <div class="m-auto my-1 basis-full self-auto rounded py-1 text-left">
                            {{-- container --}}
                            <div class="flex flex-wrap">
                                {{-- row 1 --}}
                                <div class="h-12 basis-full">
                                    <div class="flex h-12 flex-nowrap items-center text-center">
                                        <div class="xs:basis-1/4 sm:block sm:basis-3/12 md:basis-2/12">
                                            {{ __('Serial number') }}
                                        </div>
                                        <div class="xs:hidden sm:hidden sm:basis-1/12 md:block md:basis-2/12">
                                            {{ __('City') }}
                                        </div>
                                        <div class="xs:hidden sm:hidden sm:basis-1/12 md:block md:basis-2/12">
                                            {{ __('Purchase place') }}
                                        </div>
                                        <div class="xs:w-basis-2/4 sm:block sm:basis-4/12 md:basis-2/12">
                                            {{ __('Tool name') }}
                                        </div>
                                        <div class="xs:hidden sm:block sm:basis-3/12 md:basis-2/12">
                                            {{ __('Warrantee') }}
                                        </div>
                                        <div class="xs:basis-1/4 sm:block sm:basis-2/12 md:basis-2/12">
                                            {{ __('Actions') }}
                                        </div>
                                    </div>
                                </div>
                                {{-- row 2 --}}
                                @forelse ($user->products as $product)
                                    <div class="h-20 basis-full rounded py-5 text-center odd:bg-white even:bg-gray-200">
                                        <div class="flex h-20 flex-nowrap items-center sm:h-12">
                                            <div class="xs:basis-1/4 sm:block sm:basis-3/12 md:basis-2/12">
                                                {{ $product->serial_number }}
                                            </div>
                                            <div class="xs:hidden sm:hidden sm:basis-1/12 md:block md:basis-2/12">
                                                {{ $product->city }}
                                            </div>
                                            <div class="xs:hidden sm:hidden sm:basis-1/12 md:block md:basis-2/12">
                                                {{ $product->purchase_place }}
                                            </div>
                                            <div class="xs:w-basis-2/4 sm:block sm:basis-4/12 md:basis-2/12">
                                                {{ $product->tool->name }}
                                            </div>
                                            <div class="xs:hidden sm:block sm:basis-3/12 md:basis-2/12">
                                                {{ $product->serializeDate($product->warrantee_date) }}
                                            </div>
                                            <div class="xs:basis-1/4 xs:text-base sm:block sm:basis-2/12 md:basis-2/12">
                                                <x-primary-button class="bg-primary" type="button"
                                                    x-data=""
                                                    x-on:click.prevent="$dispatch('open-modal','{{ 'confirm-user-move-' . $product->id . '-' . $user->id }}')">{{ __('Moving to') }}</x-danger-button>

                                                    <x-modal :name="'confirm-user-move-' . $product->id . '-' . $user->id" :show="$errors->productMove->isNotEmpty()" focusable>
                                                        <form class="p-6" method="POST"
                                                            action="{{ route('organizations.productMove') }}">
                                                            @csrf
                                                            <h2 class="text-lg font-medium text-gray-900">
                                                                {{ __('Are you sure you want to move the product?') }}
                                                            </h2>
                                                            <input name="product_id" type="hidden"
                                                                value="{{ $product->id }}">
                                                            <input name="user_id" type="hidden"
                                                                value="{{ $user->id }}">

                                                            <div class="mt-6">
                                                                <x-select-input name="selected_user_id" headText="User">
                                                                    @foreach ($user->organization->users as $user_2)
                                                                        <x-select-input-option :value="$user_2->id">
                                                                            {{ $user_2->name }}
                                                                        </x-select-input-option>
                                                                    @endforeach
                                                                </x-select-input>

                                                                <x-input-error class="mt-2" :messages="$errors->productMove->get(
                                                                    'selected_user_id',
                                                                )" />
                                                            </div>

                                                            <div class="mt-6 flex justify-end">
                                                                <x-secondary-button x-on:click="$dispatch('close')">
                                                                    {{ __('Cancel') }}
                                                                </x-secondary-button>
                                                                <x-secondary-button
                                                                    class="bg-orange text-white hover:bg-primary-500"
                                                                    type="subbmit">
                                                                    {{ __('Move') }}
                                                                </x-secondary-button>
                                                                <x-danger-button class="ml-3">
                                                                    <a href="{{ route('organizations.detach', [
                                                                        'organization' => $organization->id,
                                                                        'product' => $product->id,
                                                                        'user' => $user->id,
                                                                    ]) }}"
                                                                        wire:navigate>{{ __('Remove product from user') }}</a>
                                                                </x-danger-button>
                                                            </div>

                                                        </form>
                                                    </x-modal>

                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="h-12 basis-full bg-gray-200 text-center">
                                        <div class="flex h-12 flex-nowrap items-center">
                                            <div class="basis-full">
                                                {{ __('no product found') }}
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.app>
