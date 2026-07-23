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
                    <tr class="odd:bg-white even:bg-gray-200 dark:odd:bg-gray-800 dark:even:bg-gray-700">
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
                        </td>
                    </tr>
                @endforeach
                {{ $tools->links() }}
            </tbody>

        </table>
    @endif
</div>
