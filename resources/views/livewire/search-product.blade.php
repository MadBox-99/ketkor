<div>
    <div class="mt-8 space-y-4 px-4">
        <x-input-label for="owner_name_search">
            {{ __('Product name') }}
        </x-input-label>
        <input class="border w-full border-solid border-gray-300 p-2 md:w-1/4" type="text" placeholder="Search products"
            wire:model.live="owner_name" name="owner_name_search" />

        <div wire:loading>Searching products...</div>
        @if (sizeof($products) > 0)
            <table class="w-full table-auto border-collapse text-sm">
                <thead>
                    <tr>

                        <x-th-field class="basis-1/12">
                            serial number
                        </x-th-field>
                        <x-th-field class="basis-1/12">
                            owner name
                        </x-th-field>
                        <x-th-field class="basis-1/12">
                            installer
                        </x-th-field>
                        <x-th-field class="basis-1/12">
                            city
                        </x-th-field>
                        <x-th-field class="basis-1/12">
                            street
                        </x-th-field>
                        <x-th-field class="basis-1/12">
                            zip
                        </x-th-field>
                        <x-th-field class="basis-1/12">
                            warrantee date
                        </x-th-field>
                        <x-th-field class="basis-1/12">
                            installation date
                        </x-th-field>
                        <x-th-field class="basis-1/12">
                            actions
                        </x-th-field>
                    </tr>
                </thead>
                <tbody class="dark:bg-slate-800 bg-white">
                    @foreach ($products as $product)
                        <tr class="odd:bg-white even:bg-gray-200">
                            <x-table-td class="basis-1/12">
                                {{ $product->serial_number }}
                            </x-table-td>
                            <x-table-td class="basis-1/12">
                                {{ $product->owner_name }}
                            </x-table-td>
                            <x-table-td class="basis-1/12">
                                {{ $product->installer_name }}
                            </x-table-td>
                            <x-table-td class="basis-1/12">
                                {{ $product->city }}
                            </x-table-td>
                            <x-table-td class="basis-1/12">
                                {{ $product->street }}
                            </x-table-td>
                            <x-table-td class="basis-1/12">
                                {{ $product->zip }}
                            </x-table-td>
                            <x-table-td class="basis-1/12">
                                {{ $product->serializeDate($product->warrantee_date) }}
                            </x-table-td>
                            <x-table-td class="basis-1/12">
                                {{ $product->serializeDate($product->installation_date) }}
                            </x-table-td>
                            <td class="flex">
                                <a wire:navigate href="{{ route('products.edit', ['product' => $product->id]) }}"
                                    class="btn btn-primary m-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </a>
                                <form method="POST"
                                    action="{{ route('products.destroy', ['product' => $product->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger m-2" type="submit">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    {{ $products->links() }}
                </tbody>
        @endif
    </div>
</div>
