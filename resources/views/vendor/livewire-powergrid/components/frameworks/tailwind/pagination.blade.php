<div class="items-center justify-between sm:flex">
    <div class="w-full items-center justify-between sm:flex sm:flex-1">
        @if ($recordCount === 'full')
            <div>
                <div
                    class="text-pg-primary-700 text-md dark:text-pg-primary-300 mr-2 text-center leading-5 sm:text-right">
                    {{ trans('livewire-powergrid::datatable.pagination.showing') }}
                    <span class="firstItem font-semibold">{{ $paginator->firstItem() }}</span>
                    {{ trans('livewire-powergrid::datatable.pagination.to') }}
                    <span class="lastItem font-semibold">{{ $paginator->lastItem() }}</span>
                    {{ trans('livewire-powergrid::datatable.pagination.of') }}
                    <span class="total font-semibold">{{ $paginator->total() }}</span>
                    {{ trans('livewire-powergrid::datatable.pagination.results') }}
                </div>
            </div>
        @elseif($recordCount === 'short')
            <div>
                <p class="text-pg-primary-700 text-md dark:text-pg-primary-300 mr-2 text-center leading-5">
                    <span class="firstItem font-semibold"> {{ $paginator->firstItem() }}</span>
                    -
                    <span class="lastItem font-semibold"> {{ $paginator->lastItem() }}</span>
                    |
                    <span class="total font-semibold"> {{ $paginator->total() }}</span>

                </p>
            </div>
        @elseif($recordCount === 'min')
            <div>
                <p class="text-pg-primary-700 text-md dark:text-pg-primary-300 mr-2 text-center leading-5">
                    <span class="firstItem font-semibold"> {{ $paginator->firstItem() }}</span>
                    -
                    <span class="lastItem font-semibold"> {{ $paginator->lastItem() }}</span>
                </p>
            </div>
        @endif

        @if ($paginator->hasPages() && $recordCount != 'min')
            <nav class="items-center justify-between sm:flex" role="navigation" aria-label="Pagination Navigation">
                <div class="mt-2 flex justify-center sm:mt-0 md:flex-none md:justify-end">

                    @if (!$paginator->onFirstPage())
                        <a class="bg-pg-primary-600 border-pg-primary-400 hover:bg-pg-primary-600 hover:border-pg-primary-800 dark:text-pg-primary-300 m-1 cursor-pointer rounded border-1 px-2 py-1 pt-2 text-center text-white"
                            wire:click="gotoPage(1)">
                            <x-livewire-powergrid::icons.chevron-double-left />
                        </a>

                        <a class="bg-pg-primary-600 border-pg-primary-400 hover:bg-pg-primary-600 hover:border-pg-primary-800 dark:text-pg-primary-300 m-1 cursor-pointer rounded border-1 px-2 py-1 pt-2 text-center text-white"
                            wire:click="previousPage" rel="next">
                            <svg class="bi bi-chevron-compact-left" xmlns="http://www.w3.org/2000/svg" width="16"
                                height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M9.224 1.553a.5.5 0 0 1 .223.67L6.56 8l2.888 5.776a.5.5 0 1 1-.894.448l-3-6a.5.5 0 0 1 0-.448l3-6a.5.5 0 0 1 .67-.223z" />
                            </svg>
                        </a>
                    @endif

                    @foreach ($elements as $element)
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($paginator->currentPage() > 3 && $page === 2)
                                    <div class="text-pg-primary-800 dark:text-pg-primary-300 mx-1 mt-1">
                                        <span class="font-bold">.</span>
                                        <span class="font-bold">.</span>
                                        <span class="font-bold">.</span>
                                    </div>
                                @endif

                                @if ($page == $paginator->currentPage())
                                    <span
                                        class="border-pg-primary- dark:bg-pg-primary-800 dark:text-pg-primary-300 m-1 cursor-pointer rounded border-1 px-2 py-1 text-center dark:text-white">{{ $page }}</span>
                                @elseif (
                                    $page === $paginator->currentPage() + 1 ||
                                        $page === $paginator->currentPage() + 2 ||
                                        $page === $paginator->currentPage() - 1 ||
                                        $page === $paginator->currentPage() - 2)
                                    <a class="bg-pg-primary-600 border-pg-primary-400 hover:bg-pg-primary-600 hover:border-pg-primary-800 dark:text-pg-primary-300 m-1 cursor-pointer rounded border-1 px-2 py-1 text-center text-white"
                                        wire:click="gotoPage({{ $page }})">{{ $page }}</a>
                                @endif

                                @if ($paginator->currentPage() < $paginator->lastPage() - 2 && $page === $paginator->lastPage() - 1)
                                    <div class="text-pg-primary-600 dark:text-pg-primary-300 mx-1 mt-1">
                                        <span>.</span>
                                        <span>.</span>
                                        <span>.</span>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        @if ($paginator->lastPage() - $paginator->currentPage() >= 2)
                            <a class="bg-pg-primary-600 border-pg-primary-400 hover:bg-pg-primary-600 hover:border-pg-primary-800 dark:text-pg-primary-300 m-1 cursor-pointer rounded border-1 px-2 py-1 pt-2 text-center text-white"
                                wire:click="nextPage" rel="next">
                                <svg class="bi bi-chevron-compact-right" xmlns="http://www.w3.org/2000/svg"
                                    width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M6.776 1.553a.5.5 0 0 1 .671.223l3 6a.5.5 0 0 1 0 .448l-3 6a.5.5 0 1 1-.894-.448L9.44 8 6.553 2.224a.5.5 0 0 1 .223-.671z" />
                                </svg>
                            </a>
                        @endif
                        <a class="bg-pg-primary-600 border-pg-primary-400 hover:bg-pg-primary-600 hover:border-pg-primary-800 dark:text-pg-primary-300 m-1 cursor-pointer rounded border-1 px-2 py-1 pt-2 text-center text-white"
                            wire:click="gotoPage({{ $paginator->lastPage() }})">
                            <x-livewire-powergrid::icons.chevron-double-right />
                        </a>
                    @endif
                </div>
            </nav>
        @endif

        <div>
            @if ($paginator->hasPages() && $recordCount == 'min')
                <nav class="items-center justify-between sm:flex" role="navigation" aria-label="Pagination Navigation">
                    <div class="mt-2 flex justify-center sm:mt-0 md:flex-none md:justify-end">
                        <span>
                            {{-- Previous Page Link Disabled --}}
                            @if ($paginator->onFirstPage())
                                <button
                                    class="text-pg-primary-400 bg-pg-primary-200 border-pg-primary-400 dark:text-pg-primary-300 m-1 rounded border-1 p-2 text-center"
                                    disabled>
                                    <x-livewire-powergrid::icons.chevron-double-left />
                                </button>
                            @else
                                @if (method_exists($paginator, 'getCursorName'))
                                    <button
                                        class="bg-pg-primary-600 border-pg-primary-400 hover:bg-pg-primary-600 hover:border-pg-primary-800 dark:text-pg-primary-300 m-1 cursor-pointer rounded border-1 p-2 text-center text-white"
                                        wire:click="setPage('{{ $paginator->previousCursor()->encode() }}','{{ $paginator->getCursorName() }}')"
                                        wire:loading.attr="disabled">
                                        <x-livewire-powergrid::icons.chevron-double-left />
                                    </button>
                                @else
                                    <button
                                        class="bg-pg-primary-600 border-pg-primary-400 hover:bg-pg-primary-600 hover:border-pg-primary-800 dark:text-pg-primary-300 m-1 cursor-pointer rounded border-1 p-2 text-center text-white"
                                        wire:click="previousPage('{{ $paginator->getPageName() }}')"
                                        wire:loading.attr="disabled">
                                        <x-livewire-powergrid::icons.chevron-double-left />
                                    </button>
                                @endif
                            @endif
                        </span>

                        <span>
                            {{-- Next Page Link --}}
                            @if ($paginator->hasMorePages())
                                @if (method_exists($paginator, 'getCursorName'))
                                    <button
                                        class="bg-pg-primary-600 border-pg-primary-400 hover:bg-pg-primary-600 hover:border-pg-primary-800 dark:text-pg-primary-300 m-1 cursor-pointer rounded border-1 p-2 text-center text-white"
                                        wire:click="setPage('{{ $paginator->nextCursor()->encode() }}','{{ $paginator->getCursorName() }}')"
                                        wire:loading.attr="disabled">
                                        <x-livewire-powergrid::icons.chevron-double-right />
                                    </button>
                                @else
                                    <button
                                        class="bg-pg-primary-600 border-pg-primary-400 hover:bg-pg-primary-600 hover:border-pg-primary-800 dark:text-pg-primary-300 m-1 cursor-pointer rounded border-1 p-2 text-center text-white"
                                        wire:click="nextPage('{{ $paginator->getPageName() }}')"
                                        wire:loading.attr="disabled">
                                        <x-livewire-powergrid::icons.chevron-double-right />
                                    </button>
                                @endif
                            @else
                                <button
                                    class="text-pg-primary-400 bg-pg-primary-200 border-pg-primary-400 dark:text-pg-primary-300 m-1 rounded border-1 p-2 text-center"
                                    disabled>
                                    <x-livewire-powergrid::icons.chevron-double-right />
                                </button>
                            @endif
                        </span>
                    </div>
                </nav>
            @endif
        </div>
    </div>
</div>
