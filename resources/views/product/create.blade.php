<x-app-layout>
    <div class="">

        <!-- Page Heading -->
        <x-slot name="header">
            <x-button-style-link text="Add product" route="products.index">
                Back
            </x-button-style-link>
        </x-slot>
        {{-- Alert Messages --}}
        <x-alert />
        <div class="card mb-4 shadow">
            <div class="flex min-h-screen items-center justify-center">
                <div class="w-full max-w-xs">
                    <form method="POST" action="{{ route('products.store') }}"
                        class="mb-4 rounded bg-white px-8 pb-8 pt-6 shadow-md">
                        @csrf
                        <div class="mb-4">
                            <x-create-input-text name="serial_number" headText="Serial number"></x-create-input-text>
                            <x-create-input-text name="owner_name" headText="Owner name"></x-create-input-text>
                            <x-create-input-text name="city" headText="City"></x-create-input-text>
                            <x-create-input-text name="street" headText="Street"></x-create-input-text>
                            <x-create-input-text name="zip" headText="Zip"></x-create-input-text>
                            <x-create-input-text name="email" headText="Email"></x-create-input-text>
                            <div class="col-sm-6 mb-sm-0 mb-3">
                                <label for="phone"> <span style="color:red;">*</span>{{ __('Phone') }}</label>
                                <input type="tel"
                                    class="border @error('phone') border-l-danger-600 @enderror block w-full rounded border-0 p-2 py-1.5 text-2xl text-gray-900 shadow ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    name="phone" value="{{ !empty(old('phone')) ? old('phone') : '' }}">
                            </div>
                            <div class="col-sm-6 mb-sm-0 mb-3">
                                <label for="purchase_date"> <span
                                        style="color:red;">*</span>{{ __('Purchase date') }}</label>
                                <input type="date"
                                    class="border @error('purchase_date') border-l-danger-600 @enderror block w-full rounded border-0 p-2 py-1.5 text-2xl text-gray-900 shadow ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    name="purchase_date"
                                    value="{{ !empty(old('purchase_date')) ? old('purchase_date') : '' }}">
                            </div>
                            <div class="col-sm-6 mb-sm-0 mb-3">
                                <label for="installation_date"> <span
                                        style="color:red;">*</span>{{ __('Installation date') }}</label>
                                <input type="date"
                                    class="border @error('installation_date') border-l-danger-600 @enderror block w-full rounded border-0 p-2 py-1.5 text-2xl text-gray-900 shadow ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    name="installation_date"
                                    value="{{ !empty(old('installation_date')) ? old('installation_date') : '' }}">
                            </div>
                            <div class="col-sm-6 mb-sm-0 mb-3">
                                <label for="warrantee_date"> <span
                                        style="color:red;">*</span>{{ __('Warrantee date') }}</label>
                                <input type="date"
                                    class="border @error('warrantee_date') border-l-danger-600 @enderror block w-full rounded border-0 p-2 py-1.5 text-2xl text-gray-900 shadow ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                    name="warrantee_date"
                                    value="{{ !empty(old('warrantee_date')) ? old('warrantee_date') : '' }}">
                            </div>
                            <x-select-input name="user_id" headText="User">
                                @foreach ($users as $user)
                                    <x-select-input-option value="{{ $user->id }}">
                                        {{ $user->name }}
                                    </x-select-input-option>
                                @endforeach
                            </x-select-input>
                            <x-select-input name="tool_id" headText="Tool">
                                @foreach ($tools as $tool)
                                    <x-select-input-option value="{{ $tool->id }}">
                                        {{ $tool->name }}
                                    </x-select-input-option>
                                @endforeach
                            </x-select-input>
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
