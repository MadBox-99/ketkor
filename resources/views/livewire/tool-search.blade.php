<div>
    <x-input-label for="owner_name_search">
        {{ __('Tool name') }}
    </x-input-label>
    <input class="border w-full border-solid border-gray-300 p-2 md:w-1/4" name="owner_name_search" type="text"
        placeholder="{{ __('Search tools') }}" wire:model.live="tool_name" />

    <div wire:loading>{{__('Searching tools...')}}</div>
    @if (sizeof($tools) > 0)
        <table class="w-full table-auto border-collapse text-sm">
            <thead>
                <tr>
                    <x-th-field>
                        name
                    </x-th-field>
                    <x-th-field>
                        category
                    </x-th-field>
                    <x-th-field>
                        tag
                    </x-th-field>
                    <x-th-field>
                        factory name
                    </x-th-field>
                    <x-th-field>
                        actions
                    </x-th-field>
                </tr>
            </thead>
            <tbody class="dark:bg-slate-800 bg-white">
                @foreach ($tools as $tool)
                    <tr class="odd:bg-white even:bg-gray-200">
                        <x-table-td>
                            {{ $tool->name }}
                        </x-table-td>
                        <x-table-td>
                            {{ $tool->category }}
                        </x-table-td>
                        <x-table-td>
                            {{ $tool->tag }}
                        </x-table-td>
                        <x-table-td>
                            {{ $tool->factory_name }}
                        </x-table-td>
                        <td class="hidden sm:flex sm:basis-1/4 md:basis-1/6 xl:basis-1/12">
                            <a class="m-2 basis-1/2 self-center"href="{{ route('tools.edit', ['tool' => $tool->id]) }}"
                                style="text-align: -webkit-center;" wire:navigate>
                                <x-svg.eye />
                            </a>
                            <form class="m-2 basis-1/2 self-center" method="POST"
                                action="{{ route('tools.destroy', ['tool' => $tool->id]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger m-2" type="submit">
                                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                {{ $tools->links() }}
            </tbody>

        </table>
    @endif
</div>
