<div class="mt-8 space-y-4 px-8">
    <form action="" method="GET" wire:submit="find" class="flex flex-wrap justify-center">
        <div class="flex w-full basis-full gap-1 align-middle sm:gap-2 xl:max-w-4xl">
            <input class="border flex basis-5/6 border-solid border-gray-300 p-2 text-2xl xs:text-base sm:text-3xl"
                type="text" placeholder="{{ __('Search product') }}" wire:model="serial_number" name="serial_number" />

            <button
                class="focus:shadow-outline flex basis-1/6 items-center justify-center rounded bg-primary font-bold text-white hover:bg-blue-700 focus:outline-none"
                type="submit"><x-search-icon /></button>
        </div>

        <div wire:loading>{{ __('Searching product...') }}</div>

        @if ($product !== null && $product->exists())
            <div
                class="lg:min-w-4xl mt-12 flex basis-full flex-wrap justify-between sm:text-lg md:max-w-full md:text-3xl">
                <div class="basis-1/2 sm:basis-1/2 md:basis-2/3">
                    <p class="basis-full text-gray-500">{{ $product->zip . ' ' . $product->city }}</p>
                    <p class="basis-full text-gray-500">{{ $product->street }}</p>
                    <p class="basis-full text-gray-500">{{ $product->warrantee_date }}</p>
                </div>
                @if ($owns < 1)
                    <a wire:navigate href="{{ route('products.add', ['product' => $product->id]) }}">
                        <button
                            class="focus:shadow-outline md:max-h-18 inline max-h-10 items-center justify-center rounded bg-primary px-4 font-bold text-white hover:bg-blue-700 focus:outline-none xs:text-base sm:h-10 sm:max-h-14 sm:basis-1/2 sm:text-3xl md:h-10 md:basis-2/3 md:text-2xl">
                            {{ __('add to me') }}
                        </button>
                    </a>
                @else
                    <a wire:navigate href="{{ route('products.edit', ['product' => $product->id]) }}"
                        class="btn btn-primary m-2">
                        <button
                            class="focus:shadow-outline md:max-h-18 inline max-h-10 items-center justify-center rounded bg-primary px-4 font-bold text-white hover:bg-blue-700 focus:outline-none xs:text-base sm:h-10 sm:max-h-14 sm:basis-1/2 sm:text-3xl md:h-10 md:basis-1/3 md:text-2xl">
                            {{ __('Open') }}
                        </button>
                    </a>
                @endif

            </div>
        @endif
    </form>
</div>
