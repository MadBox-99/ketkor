<div>
    <div class="flex gap-1">
        <div class="basis-1/6">
            <x-input-label for="organization_name">
                {{ __('Organization name') }}
            </x-input-label>
            <input class="border name= w-full border-solid border-gray-300 p-2" name="organization_name" type="text"
                placeholder="{{ __('Search organizaions') }}" wire:model.live="organization_name" />
        </div>

        <div class="basis-1/6">
            <x-input-label for="city">
                {{ __('Organization city') }}
            </x-input-label>
            <input class="border name= w-full border-solid border-gray-300 p-2" name="city" type="text"
                placeholder="{{ __('Search city') }}" wire:model.live="city" />
        </div>
    </div>

    <div wire:loading>{{ __('Searching organizaions...') }}</div>
    <table class="w-full table-auto border-collapse text-sm">
        <thead class="bg-primary-200">
            <tr>
                <x-th-field>name</x-th-field>
                <x-th-field>city</x-th-field>
                <x-th-field>tax number</x-th-field>
                <x-th-field>address</x-th-field>
                <x-th-field>zip</x-th-field>
                <x-th-field>actions</x-th-field>
            </tr>
        </thead>
        <tbody class="dark:bg-slate-800 bg-white">
            @foreach ($organizations as $organization)
                <tr class="odd:bg-white even:bg-gray-200">
                    <x-table-td>
                        {{ $organization->name }}
                    </x-table-td>
                    <x-table-td>
                        {{ $organization->city }}
                    </x-table-td>
                    <x-table-td>
                        {{ $organization->tax_number }}
                    </x-table-td>
                    <x-table-td>
                        {{ $organization->address }}
                    </x-table-td>
                    <x-table-td>
                        {{ $organization->zip }}
                    </x-table-td>
                    <td class="flex justify-center">
                        <a class="btn btn-primary m-2"
                            href="{{ route('organizations.edit', ['organization' => $organization->id]) }}"
                            wire:navigate>
                            <x-svg.eye />
                        </a>
                        <form method="POST"
                            action="{{ route('organizations.destroy', ['organization' => $organization->id]) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger m-2" type="submit">
                                <x-svg.trash />
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            {{ $organizations->links() }}
        </tbody>
    </table>
</div>
