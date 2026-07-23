<div>
    <x-slot name="header">
        <x-button-style-link text="Organization edit" route="organizations.employee.create">
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
                    <form class="mb-4 flex basis-full flex-wrap justify-center rounded bg-white px-8 pb-8 pt-6 shadow-md dark:bg-gray-800"
                        wire:submit="updateOrganization">
                        <div class="basis-full text-left text-xl">
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
                @foreach ($organization->users ?? [] as $user)
                    <div wire:key="user-{{ $user->id }}"
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
                                    <button type="button"
                                        wire:click="removeMember({{ $user->id }})"
                                        wire:confirm="{{ __('Are you sure you want to remove this user from your organization?') }}">{{ __('user delete') }}</button>
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
                                @forelse ($user->products ?? [] as $product)
                                    <div wire:key="product-{{ $product->id }}"
                                        class="h-20 basis-full rounded py-5 text-center odd:bg-white even:bg-gray-200">
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
                                                    x-on:click.prevent="$dispatch('open-modal','{{ 'confirm-user-move-' . $product->id . '-' . $user->id }}')">{{ __('Moving to') }}</x-primary-button>

                                                    <x-modal :name="'confirm-user-move-' . $product->id . '-' . $user->id" focusable>
                                                        <form class="p-6"
                                                            x-data="{ selectedUserId: {{ optional($user->organization->users->first())->id ?? 'null' }} }"
                                                            wire:submit="moveProduct({{ $product->id }}, {{ $user->id }}, selectedUserId)">
                                                            <h2 class="text-lg font-medium text-gray-900">
                                                                {{ __('Are you sure you want to move the product?') }}
                                                            </h2>

                                                            <div class="mt-6">
                                                                <x-filament-forms::field-wrapper :label="__('User')" :required="true" id="selected_user_id">
                                                                    <x-filament::input.wrapper>
                                                                        <x-filament::input.select id="selected_user_id" x-model="selectedUserId">
                                                                            @foreach ($user->organization->users ?? [] as $user_2)
                                                                                <x-select-input-option :value="$user_2->id">
                                                                                    {{ $user_2->name }}
                                                                                </x-select-input-option>
                                                                            @endforeach
                                                                        </x-filament::input.select>
                                                                    </x-filament::input.wrapper>
                                                                </x-filament-forms::field-wrapper>
                                                            </div>

                                                            <div class="mt-6 flex justify-end">
                                                                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                                                                    {{ __('Cancel') }}
                                                                </x-secondary-button>
                                                                <x-secondary-button
                                                                    class="bg-orange text-white hover:bg-primary-500"
                                                                    type="submit">
                                                                    {{ __('Move') }}
                                                                </x-secondary-button>
                                                                <x-danger-button class="ml-3" type="button"
                                                                    wire:click="detachProduct({{ $user->id }}, {{ $product->id }})"
                                                                    wire:confirm="{{ __('Are you sure you want to remove this product from the user?') }}">
                                                                    {{ __('Remove product from user') }}
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
</div>
