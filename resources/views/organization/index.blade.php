<x-app-layout>
    <x-slot name="header">
        <x-button-style-link text="Organizations" route="organizations.create"> New organization
            create</x-button-style-link>
    </x-slot>
    <div class="mx-20 flex max-w-full flex-row justify-center px-20 py-2">
        <div class="relative overflow-auto rounded-xl">
            <div class="my-8 overflow-hidden shadow-sm">
                <table class="w-full table-auto border-collapse text-sm">
                    <thead class="bg-primary-200">
                        <tr>
                            <x-th-field>name</x-th-field>
                            <x-th-field>city</x-th-field>
                            <x-th-field>tax number</x-th-field>
                            <x-th-field>address</x-th-field>
                            <x-th-field>zip</x-th-field>
                            <x-th-field>
                                actions
                            </x-th-field>
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
                                <td class="flex">
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
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
