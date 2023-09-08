<x-app-layout>
    <div class="">
        <!-- Page Heading -->
        <x-slot name="header">
            @role('Admin|Operator')
                <x-button-style-link text="Edit product" route="products.index">
                    Back
                </x-button-style-link>
            @else
                <x-button-style-link text="Edit product" route="products.myproducts">
                    Back
                </x-button-style-link>
            @endrole
        </x-slot>
        {{-- Alert Messages --}}
        <x-alert />
        <div class="mb-4 shadow">
            <div class="flex min-h-screen items-center justify-center">
                <div class="w-full max-w-xs">
                    <form method="POST" action="{{ route('products.update', ['product' => $product->id]) }}"
                        class="mb-4 rounded bg-white px-8 pb-8 pt-6 shadow-md">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <x-create-input-text name="serial_number" headText="Serial number">
                                {{ $product->serial_number }}
                            </x-create-input-text>
                            <x-create-input-text name="city" headText="City">
                                {{ $product->city }}
                            </x-create-input-text>
                            <x-create-input-text name="street" headText="Street">
                                {{ $product->street }}
                            </x-create-input-text>
                            <x-create-input-text name="zip" headText="Zip">
                                {{ $product->zip }}
                            </x-create-input-text>
                            <div class="basis-full">
                                <div class="col-span-full">
                                    <label for="purchase_date"
                                        class="my-5 block text-left text-sm font-medium leading-6 text-gray-900">
                                        <span style="color:red;">*</span>{{ __('Purchase date') }}</label>
                                    <div class="mt-2">
                                        <div
                                            class="flex rounded-md shadow-sm focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                            <input type="date"
                                                class="@error('purchase_date') border-l-danger-600 @enderror block w-full rounded border-0 p-2 py-1.5 text-2xl text-gray-900 shadow ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                name="purchase_date"
                                                value="{{ $product->serializeDate($product->purchase_date) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="basis-full">
                                <div class="col-span-full">
                                    <label for="purchase_date"
                                        class="my-5 block text-left text-sm font-medium leading-6 text-gray-900">
                                        <span style="color:red;">*</span>
                                        {{ __('Installation date') }}
                                    </label>
                                    <div class="mt-2">
                                        <div
                                            class="flex rounded-md shadow-sm focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                            <input type="date"
                                                class="@error('installation_date') border-l-danger-600 @enderror block w-full rounded border-0 p-2 py-1.5 text-2xl text-gray-900 shadow ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                name="installation_date"
                                                value="{{ $product->serializeDate($product->installation_date) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="basis-full">
                                <div class="col-span-full">
                                    <label for="purchase_date"
                                        class="my-5 block text-left text-sm font-medium leading-6 text-gray-900">
                                        <span style="color:red;">*</span>
                                        {{ __('Warrantee date') }}
                                    </label>
                                    <div class="mt-2">
                                        <div
                                            class="flex rounded-md shadow-sm focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                            <input type="date"
                                                class="@error('warrantee_date') border-l-danger-600 @enderror block w-full rounded border-0 p-2 py-1.5 text-2xl text-gray-900 shadow ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                                name="warrantee_date"
                                                value="{{ $product->serializeDate($product->warrantee_date) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="@role('Servicer|Organizer')hidden @endrole basis-full">
                                <div class="col-span-full">
                                    <table class="w-full table-auto border-collapse text-sm">
                                        <tbody class="dark:bg-slate-800 bg-white">
                                            @foreach ($product->users as $user)
                                                <div class="mb-[0.125rem] block min-h-[1.5rem] pl-[1.5rem]">
                                                    <input name="user_ids[]"
                                                        class="border-neutral-300 before:bg-transparent checked:after:bg-transparent checked:focus:after:bg-transparent dark:border-neutral-600 relative float-left -ml-[1.5rem] mr-[6px] mt-[0.15rem] h-[1.125rem] w-[1.125rem] appearance-none rounded-[0.25rem] border-[0.125rem] border-solid outline-none before:pointer-events-none before:absolute before:h-[0.875rem] before:w-[0.875rem] before:scale-0 before:rounded-full before:opacity-0 before:shadow-[0px_0px_0px_13px_transparent] before:content-[''] checked:border-primary checked:bg-primary checked:before:opacity-[0.16] checked:after:absolute checked:after:-mt-px checked:after:ml-[0.25rem] checked:after:block checked:after:h-[0.8125rem] checked:after:w-[0.375rem] checked:after:rotate-45 checked:after:border-[0.125rem] checked:after:border-l-0 checked:after:border-t-0 checked:after:border-solid checked:after:border-white checked:after:content-[''] hover:cursor-pointer hover:before:opacity-[0.04] hover:before:shadow-[0px_0px_0px_13px_rgba(0,0,0,0.6)] focus:shadow-none focus:transition-[border-color_0.2s] focus:before:scale-100 focus:before:opacity-[0.12] focus:before:shadow-[0px_0px_0px_13px_rgba(0,0,0,0.6)] focus:before:transition-[box-shadow_0.2s,transform_0.2s] focus:after:absolute focus:after:z-[1] focus:after:block focus:after:h-[0.875rem] focus:after:w-[0.875rem] focus:after:rounded-[0.125rem] focus:after:content-[''] checked:focus:before:scale-100 checked:focus:before:shadow-[0px_0px_0px_13px_#3b71ca] checked:focus:before:transition-[box-shadow_0.2s,transform_0.2s] checked:focus:after:-mt-px checked:focus:after:ml-[0.25rem] checked:focus:after:h-[0.8125rem] checked:focus:after:w-[0.375rem] checked:focus:after:rotate-45 checked:focus:after:rounded-none checked:focus:after:border-[0.125rem] checked:focus:after:border-l-0 checked:focus:after:border-t-0 checked:focus:after:border-solid checked:focus:after:border-white dark:checked:border-primary dark:checked:bg-primary dark:focus:before:shadow-[0px_0px_0px_13px_rgba(255,255,255,0.4)] dark:checked:focus:before:shadow-[0px_0px_0px_13px_#3b71ca]"
                                                        type="checkbox" value="{{ $user->id }}" id="user_id"
                                                        @checked(true) />
                                                    <label class="inline-block pl-[0.15rem] hover:cursor-pointer"
                                                        for="user_id">
                                                        {{ $user->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </tbody>

                                    </table>
                                </div>
                            </div>

                            <x-select-input name="tool_id" headText="Tool">
                                @foreach ($tools as $tool)
                                    <x-select-input-option :value="$tool->id" :selected="$product->tool_id == $tool->id ? true : false">
                                        {{ $tool->name }}
                                    </x-select-input-option>
                                @endforeach
                            </x-select-input>

                        </div>
                        {{-- Save Button --}}
                        <button type="submit"
                            class="w-full rounded bg-blue-500 px-4 py-2 text-center font-bold text-white hover:bg-blue-700 focus:outline-none sm:inline-block">
                            {{ __('Save') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-center">
            <div class="w-full max-w-xs">
                <form method="POST" action="{{ route('productlogs.store') }}"
                    class="mb-4 rounded bg-white px-8 pb-8 pt-6 shadow-md">
                    @csrf
                    <input type="hidden" value="{{ $product->id }}" name="product_id" id="product_id">
                    <h1 class="justify-center text-center"> {{ __('New Event') }} </h1>
                    <div class="mb-4">
                        <x-select-input name="what" headText="Operation type">
                            <x-select-input-option value="installation">
                                {{ __('Installation') }}
                            </x-select-input-option>
                            <x-select-input-option value="maintenance">
                                {{ __('Maintenance') }}
                            </x-select-input-option>
                        </x-select-input>
                        <div class="relative mb-3" data-te-input-wrapper-init>
                            <textarea name="comment"
                                class="bg-transparent dark:text-neutral-200 dark:placeholder:text-neutral-200 peer block min-h-[auto] w-full rounded border-0 px-3 py-[0.32rem] leading-[1.6] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 peer-focus:text-primary data-[te-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:peer-focus:text-primary [&:not([data-te-input-placeholder-active])]:placeholder:opacity-0"
                                id="exampleFormControlTextarea1" rows="4" placeholder=" {{ __('comment') }}"></textarea>
                            <label for="exampleFormControlTextarea1"
                                class="text-neutral-500 dark:text-neutral-200 pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[1.6] transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[te-input-state-active]:-translate-y-[0.9rem] peer-data-[te-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:peer-focus:text-primary">
                                {{ __('comment') }}
                            </label>
                        </div>
                    </div>
                    {{-- Save Button --}}
                    <button type="submit"
                        class="rounded bg-blue-500 px-4 py-2 text-center font-bold text-white hover:bg-blue-700 focus:outline-none sm:inline-block">
                        {{ __('Create') }}
                    </button>
                </form>
            </div>
        </div>
        @if (count($product->product_logs) > 0)
            <div class="mx-20 flex max-w-full flex-row justify-center px-20 py-2">
                <div class="relative overflow-auto rounded-xl">
                    <div class="my-8 overflow-hidden shadow-sm">
                        <table class="w-full table-auto border-collapse text-sm">
                            <thead class="bg-primary-200">
                                <tr>
                                    <x-th-field>
                                        event
                                    </x-th-field>
                                    <x-th-field>
                                        event content
                                    </x-th-field>
                                    <x-th-field>
                                        event time
                                    </x-th-field>
                                </tr>
                            </thead>
                            <tbody class="dark:bg-slate-800 bg-white">
                                @forelse  ($product->product_logs as $log)
                                    <tr>
                                        <x-table-td>
                                            {{ __($log->what) }}
                                        </x-table-td>
                                        <x-table-td>
                                            {{ $log->comment }}
                                        </x-table-td>
                                        <x-table-td>
                                            {{ $product->serializeDate($log->when) }}
                                        </x-table-td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <p>{{ __('No product log') }} </p>
        @endif

        <div class="flex items-center justify-center">
            <div class="w-full max-w-7xl">
                @if ($userVisibility)

                    <form method="POST" action="{{ route('partials.store') }}"
                        class="mb-4 rounded bg-white px-8 pb-8 pt-6 shadow-md">
                        @csrf
                        <input type="hidden" value="{{ $product->id }}" name="product_id" id="product_id">
                        <h1 class="justify-center text-center"> {{ __('Owner Datas') }} </h1>
                        <div class="mb-4">
                            @foreach ($partials as $partial)
                                @if ($loop->first)
                                    <x-create-input-text name="name" headText="Owner name">
                                        {{ $partial->name }}
                                    </x-create-input-text>
                                    <x-create-input-text name="email" headText="Email">
                                        {{ $partial->email }}
                                    </x-create-input-text>
                                    <x-create-input-text name="phone" headText="Mobile">
                                        {{ $partial->phone }}
                                    </x-create-input-text>
                                @endif
                            @endforeach
                            <div class="mx-20 flex max-w-full basis-full flex-row justify-center px-20 py-2">
                                <div class="relative overflow-auto rounded-xl">
                                    <div class="my-8 overflow-hidden shadow-sm">
                                        <table class="w-full table-auto border-collapse text-sm">
                                            <thead class="bg-primary-200">
                                                <tr>
                                                    <x-th-field>
                                                        name
                                                    </x-th-field>
                                                    <x-th-field>
                                                        email
                                                    </x-th-field>
                                                    <x-th-field>
                                                        mobile
                                                    </x-th-field>
                                                </tr>
                                            </thead>
                                            <tbody class="odd:bg-white even:bg-gray-200">
                                                @foreach ($partials as $partial)
                                                    @if ($loop->index > 0)
                                                        @continue
                                                    @endif
                                                    <tr>
                                                        <x-table-td>
                                                            {{ $partial->name }}
                                                        </x-table-td>
                                                        <x-table-td>
                                                            {{ $partial->email }}
                                                        </x-table-td>
                                                        <x-table-td>
                                                            {{ $partial->phone }}
                                                        </x-table-td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            {{-- Save Button --}}
                            <button type="submit"
                                class="rounded bg-blue-500 px-4 py-2 text-center font-bold text-white hover:bg-blue-700 focus:outline-none sm:inline-block">
                                {{ __('Update') }}
                            </button>
                        </div>
                    </form>
                @else
                    <a href="{{ route('accestokens.createAccessToken', ['product' => $product->id]) }}">
                        <x-primary-button>
                            Személyes adatok
                            igénylése
                        </x-primary-button>
                    </a>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
