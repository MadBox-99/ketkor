<div class="flex flex-wrap">
    <div class="mt-8 basis-full space-y-4 px-2">
        <div class="flex flex-wrap lg:gap-0">
            <div class="my-5 basis-full px-2 sm:basis-1/2 lg:basis-1/4">
                <div class="flex flex-wrap">
                    <div class="basis-full">
                        <x-input-label for="serial_number_search">
                            {{ __('Product serial number') }}
                        </x-input-label>
                    </div>
                    <div class="basis-full">
                        <input class="border type= w-full border-solid border-gray-300 p-2" name="serial_number_search"
                            placeholder="{{ __('serial number') }}" wire:model.live.throttle.1000ms="serial_number" />
                    </div>
                </div>
            </div>

            <div class="my-5 basis-full px-2 sm:basis-1/2 lg:basis-1/4">
                <div class="flex flex-wrap">
                    <div class="basis-full">
                        <x-input-label for="tool_name_search">
                            {{ __('Product tool name') }}
                        </x-input-label>
                    </div>
                    <div class="basis-full">
                        <input class="border type= w-full border-solid border-gray-300 p-2" name="tool_name_search"
                            placeholder="{{ __('Tool name') }}" wire:model.live.throttle.1000ms="tool_name" />
                    </div>
                </div>
            </div>
            <div class="my-5 basis-full px-2 sm:basis-1/2 lg:basis-1/4">
                <div class="flex flex-wrap">
                    <div class="basis-full">
                        <x-input-label for="warrantee_date_start"><span style="color:red;">*</span>
                            {{ __('Product warrantee date expire start') }}
                        </x-input-label>
                    </div>
                    <div class="basis-full">
                        <input class="border type= w-full border-solid border-gray-300 p-2" name="warrantee_date_start"
                            type="date" wire:model.live.throttle.1000ms="warrantee_date_start" />
                    </div>
                </div>
            </div>
            <div class="my-5 basis-full px-2 sm:basis-1/2 lg:basis-1/4">
                <div class="flex flex-wrap">
                    <div class="basis-full">
                        <div class="basis-1/2">
                            <x-input-label for="warrantee_date_end"><span style="color:red;">*</span>
                                {{ __('Product warrantee date expire end') }}
                            </x-input-label>
                            <div class="basis-full">
                                <input class="border type= w-full border-solid border-gray-300 p-2"
                                    name="warrantee_date_end" type="date"
                                    wire:model.live.throttle.1000ms="warrantee_date_end" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="flex basis-full flex-wrap">
        <div class="basis-full">
            @forelse ($products as $product)
                @if ($loop->first)
                    <table class="my-7 w-full table-auto border-collapse text-sm">
                        <thead>
                            <tr class="flex flex-nowrap">
                                <x-th-field class="hidden sm:table-cell sm:basis-1/4 md:basis-1/6 xl:basis-2/12">
                                    serial number
                                </x-th-field>
                                <x-th-field class="hidden sm:table-cell sm:basis-1/4 md:basis-1/6 xl:basis-3/12">
                                    owner name
                                </x-th-field>
                                <x-th-field class="hidden md:table-cell md:basis-1/6 xl:basis-1/12">
                                    type
                                </x-th-field>
                                <x-th-field class="hidden sm:hidden md:table-cell md:basis-1/6 xl:basis-2/12">
                                    product tool name
                                </x-th-field>
                                <x-th-field class="hidden sm:hidden xl:table-cell xl:basis-1/12">
                                    city
                                </x-th-field>
                                <x-th-field class="hidden sm:hidden xl:table-cell xl:basis-1/12">
                                    street
                                </x-th-field>
                                <x-th-field class="hidden sm:table-cell sm:basis-1/4 md:basis-1/6 xl:basis-1/12">
                                    warrantee date
                                </x-th-field>
                                <x-th-field class="hidden sm:table-cell sm:basis-1/4 md:basis-1/6 xl:basis-1/12">
                                    actions
                                </x-th-field>
                            </tr>
                        </thead>
                        <tbody class="dark:bg-slate-800 bg-white">
                @endif
                <tr class="my-2 flex flex-nowrap p-2 text-center odd:bg-white even:bg-gray-200">
                    <x-table-td class="hidden sm:table-cell sm:basis-1/4 md:basis-1/6 xl:basis-2/12" id='td1'>
                        {{ $product->serial_number }}
                    </x-table-td>
                    @if ($product->are_visible[0]->isVisible)
                        <x-table-td class="hidden sm:table-cell sm:basis-1/4 md:basis-1/6 xl:basis-3/12" id='td2'>
                            {{ $product->partials[0]->name }}
                        </x-table-td>
                    @else
                        <x-table-td class="hidden sm:table-cell sm:basis-1/4 md:basis-1/6 xl:basis-3/12" id='td2'>
                            <a href="{{ route('accestokens.createAccessToken', ['product' => $product->id]) }}"
                                wire:navigate>
                                <x-primary-button>
                                    {{ __('Require access') }}
                                </x-primary-button>
                            </a>
                        </x-table-td>
                    @endif
                    @if ($product->are_visible[0]->isVisible)
                        <x-table-td class="hidden md:table-cell md:basis-1/6 xl:basis-1/12" id='td3'>
                            {{ $product->tool->category }}
                        </x-table-td>
                    @else
                        <x-table-td class="hidden md:table-cell md:basis-1/6 xl:basis-1/12" id='td3' />
                    @endif
                    @if ($product->are_visible[0]->isVisible)
                        <x-table-td class="hidden sm:hidden md:table-cell md:basis-1/6 xl:basis-2/12" id='td4'>
                            {{ $product->tool->name }}
                        </x-table-td>
                    @else
                        <x-table-td class="hidden sm:hidden md:table-cell md:basis-1/6 xl:basis-2/12" id='td4' />
                    @endif
                    @if ($product->are_visible[0]->isVisible)
                        <x-table-td class="hidden sm:hidden xl:table-cell xl:basis-1/12" id='td5'>
                            {{ $product->city }}
                        </x-table-td>
                    @else
                        <x-table-td class="hidden sm:hidden xl:table-cell xl:basis-1/12" id='td5' />
                    @endif
                    @if ($product->are_visible[0]->isVisible)
                        <x-table-td class="hidden sm:hidden xl:table-cell xl:basis-1/12" id='td6'>
                            {{ $product->street }}
                        </x-table-td>
                    @else
                        <x-table-td class="hidden sm:hidden xl:table-cell xl:basis-1/12" id='td6' />
                    @endif
                    @if ($product->are_visible[0]->isVisible)
                        <x-table-td class="hidden sm:table-cell sm:basis-1/4 md:basis-1/6 xl:basis-1/12" id='td7'>
                            {{ $product->serializeDate($product->warrantee_date) }}
                        </x-table-td>
                    @else
                        <x-table-td class="hidden sm:table-cell sm:basis-1/4 md:basis-1/6 xl:basis-1/12"
                            id='td7' />
                    @endif
                    <td class="hidden sm:flex sm:basis-1/4 md:basis-1/6 xl:basis-1/12">
                        <a class="m-2 basis-1/2 self-center"
                            href="{{ route('products.edit', ['product' => $product->id]) }}"
                            style="text-align: -webkit-center;" wire:navigate>
                            <x-svg.eye />
                        </a>
                        <form class="m-2 basis-1/2 self-center" method="POST"
                            action="{{ route('products.remove', ['product' => $product->id]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit">
                                <x-svg.trash />
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
</div>
</div>
