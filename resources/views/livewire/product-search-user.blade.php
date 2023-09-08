<div>
    <div class="mt-8 space-y-4 px-4">
        <div class="inline-flex w-full flex-nowrap gap-2">
            <div class="basis-1/2 md:basis-1/4">
                <div class="flex flex-wrap">
                    <div class="basis-full">
                        <x-input-label for="serial_number_search">
                            {{ __('Product serial number') }}
                        </x-input-label>
                    </div>
                    <div class="basis-full">
                        <input class="border type= w-full border-solid border-gray-300 p-2"
                            placeholder="{{ __('serial number') }}" wire:model.live.throttle.1000ms="serial_number"
                            name="serial_number_search" />
                    </div>
                </div>

            </div>
            <div class="basis-1/2 md:basis-1/4">
                <div class="flex flex-wrap">
                    <div class="basis-full">
                        <x-input-label for="tool_name_search">
                            {{ __('Product tool name') }}
                        </x-input-label>
                    </div>
                    <div class="basis-full">
                        <input class="border type= w-full border-solid border-gray-300 p-2"
                            placeholder="{{ __('Tool name') }}" wire:model.live.throttle.1000ms="tool_name"
                            name="tool_name_search" />
                    </div>
                </div>
            </div>
        </div>

        @forelse ($products as $product)
            @if ($loop->first)
                <table class="w-full table-auto border-collapse text-sm">
                    <thead class="justify-center bg-primary-200 text-center">
                        <tr class="text-center">
                            <x-th-field
                                class="hidden sm:table-cell sm:basis-1/6 md:table-cell md:basis-1/12 xl:table-cell">
                                serial number
                            </x-th-field>
                            <x-th-field class="hidden sm:hidden sm:basis-1/6 md:hidden md:basis-1/12 xl:table-cell">
                                owner name
                            </x-th-field>
                            <x-th-field
                                class="hidden sm:table-cell sm:basis-1/6 md:table-cell md:basis-1/12 xl:table-cell">
                                installer
                            </x-th-field>
                            <x-th-field class="hidden sm:hidden sm:basis-1/6 md:table-cell md:basis-1/12 xl:table-cell">
                                city
                            </x-th-field>
                            <x-th-field class="hidden sm:hidden sm:basis-1/6 md:hidden md:basis-1/12 xl:table-cell">
                                street
                            </x-th-field>
                            <x-th-field class="hidden sm:hidden sm:basis-1/6 md:hidden md:basis-1/12 xl:table-cell">
                                zip
                            </x-th-field>
                            <x-th-field
                                class="hidden sm:table-cell sm:basis-1/6 md:table-cell md:basis-1/12 xl:table-cell">
                                warrantee date
                            </x-th-field>
                            <x-th-field class="hidden sm:hidden sm:basis-1/6 md:hidden md:basis-1/12 xl:table-cell">
                                installation date
                            </x-th-field>
                            <x-th-field
                                class="hidden sm:table-cell sm:basis-1/6 md:table-cell md:basis-1/12 xl:table-cell">
                                actions
                            </x-th-field>
                        </tr>
                    </thead>
                    <tbody class="dark:bg-slate-800 bg-white">
            @endif
            <tr class="text-center odd:bg-white even:bg-gray-200">
                <x-table-td class="hidden sm:table-cell sm:basis-1/6 md:table-cell md:basis-1/12">
                    {{ $product->serial_number }}
                </x-table-td>
                <td class="hidden sm:hidden sm:basis-1/6 md:hidden md:basis-1/12 xl:table-cell">
                    @if ($product->are_visible[0]->isVisible)
                        {{ $product->partials[0]->name }}
                    @else
                        <a href="{{ route('accestokens.createAccessToken', ['product' => $product->id]) }}">
                            <x-primary-button>
                                {{ __('Require access') }}
                            </x-primary-button>
                        </a>
                    @endif
                </td>
                <x-table-td class="hidden sm:table-cell sm:basis-1/6 md:table-cell md:basis-1/12">
                    {{ $product->installer_name }}
                </x-table-td>
                <x-table-td class="hidden sm:hidden sm:basis-1/6 md:table-cell md:basis-1/12">
                    {{ $product->city }}
                </x-table-td>
                <x-table-td class="hidden sm:hidden sm:basis-1/6 md:hidden md:basis-1/12 xl:table-cell">
                    {{ $product->street }}
                </x-table-td>
                <x-table-td class="hidden sm:hidden sm:basis-1/6 md:hidden md:basis-1/12 xl:table-cell">
                    {{ $product->zip }}
                </x-table-td>
                <x-table-td class="hidden sm:table-cell sm:basis-1/6 md:table-cell md:basis-1/12">
                    {{ $product->serializeDate($product->warrantee_date) }}
                </x-table-td>
                <x-table-td class="hidden sm:hidden sm:basis-1/6 md:hidden md:basis-1/12 xl:table-cell">
                    {{ $product->serializeDate($product->installation_date) }}
                </x-table-td>
                <td class="flex justify-center sm:flex sm:basis-1/6 md:flex md:basis-1/12">
                    <a wire:navigate href="{{ route('products.edit', ['product' => $product->id]) }}"
                        class="btn btn-primary m-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('products.remove', ['product' => $product->id]) }}">
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

            @if ($loop->last)
                </tbody>
                </table>
            @endif
    </div>
    {{ $products->links() }}
@empty
    <p>
        {{ __('You dont, have any product yet.') }}
    </p>
    @endforelse
</div>
