<x-layouts.app>
    <!-- Page Heading -->
    <x-slot name="header">
        <x-button-style-link text="Users" route="users.create">New user create</x-button-style-link>
    </x-slot>
    <x-alert />
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                <table class="w-full table-auto border-collapse rounded-2xl text-sm">
                    <thead class="rounded-lg bg-primary-100">
                        <tr class="rounded-lg text-lg">
                            <x-th-field class="rounded-tl-md py-10">
                                name
                            </x-th-field>
                            <x-th-field>
                                organization
                            </x-th-field>
                            <x-th-field>
                                rule
                            </x-th-field>
                            <x-th-field class="rounded-tr-md py-10">
                                actions
                            </x-th-field>
                        </tr>
                    </thead>
                    <tbody class="dark:bg-slate-800 bg-white">
                        @foreach ($users as $user)
                            <tr class="odd:bg-white even:bg-gray-200">
                                <x-table-td>
                                    {{ $user->name }}
                                </x-table-td>
                                <x-table-td>
                                    @if ($user->organization != null)
                                        {{ $user->organization->name }}
                                    @endif

                                </x-table-td>
                                <x-table-td>
                                    {{ __($user->getRoleNames()->first()) }}
                                </x-table-td>
                                <td class="flex">
                                    <a class="btn btn-primary m-2"
                                        href="{{ route('users.show', ['user' => $user->id]) }}" wire:navigate>
                                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('profile.destroy', ['user' => $user->id]) }}">
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
                    </tbody>
                    {{ $users->links() }}
                </table>
            </div>
        </div>
    </div>

</x-layouts.app>
