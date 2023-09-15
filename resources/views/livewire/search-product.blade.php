<div>
    <div class="mt-8 space-y-4 px-4">
        <x-input-label for="owner_name_search">
            {{ __('Owner name') }}
        </x-input-label>
        <input class="border w-full border-solid border-gray-300 p-2 md:w-1/4" name="owner_name_search" type="text"
            placeholder="{{ __('Search products') }}" wire:model.live="owner_name" />

        <div wire:loading>{{ __('Searching product...') }}</div>
        @if (sizeof($products) > 0)
            <table class="h-fit max-h-96 w-full table-auto border-collapse text-sm">
                <thead>
                    <tr>

                        <x-th-field class="basis-1/12">
                            serial number
                        </x-th-field>
                        <x-th-field class="basis-1/12">
                            tool name
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
                        <tr class="my-4 h-[4.5rem] py-4 odd:bg-white even:bg-gray-200">
                            <x-table-td class="basis-1/12">
                                {{ $product->serial_number }}
                            </x-table-td>
                            <x-table-td class="basis-1/12">
                                {{ $product->tool->name }}
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
                            <td class="basis-1/12 self-center text-center">
                                <div class="flex" class="btn btn-primary m-2"
                                    href="{{ route('products.edit', ['product' => $product->id]) }}" <a wire:navigate>
                                    <x-svg.eye />
                                    </a>
                                    <form method="POST"
                                        action="{{ route('products.destroy', ['product' => $product->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger m-2" type="submit">
                                            <x-svg.trash />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    {{ $products->links() }}
                </tbody>
            </table>
        @endif
    </div>
</div>
