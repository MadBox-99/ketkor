<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-primary">
            <div class="">
                <h1 class="mb-0 text-primary opacity-100">{{ __('Logs') }}</h1>
            </div>
        </h2>
    </x-slot>
    <x-alert />
    <div class="mx-20 flex max-w-full flex-row justify-center px-20 py-2">
        <div class="relative overflow-auto rounded-xl">
            <div class="my-8 overflow-hidden shadow-sm">
                <table class="w-full table-auto border-collapse text-sm">
                    <thead class="bg-primary-200">
                        <tr>
                            <x-th-field>
                                what
                            </x-th-field>
                            <x-th-field>
                                who
                            </x-th-field>
                            <x-th-field>
                                when
                            </x-th-field>
                            <x-th-field>
                                action
                            </x-th-field>
                        </tr>
                    </thead>
                    <tbody class="dark:bg-slate-800 bg-white">
                        @foreach ($logs as $log)
                            <tr>
                                <x-table-td>
                                    {{ $log->what }}
                                </x-table-td>
                                <x-table-td>
                                    {{ $log->user->name }}
                                </x-table-td>
                                <x-table-td>
                                    {{ $log->created_at }}
                                </x-table-td>

                                <td class="flex">
                                    <form method="POST" action="{{ route('logs.destroy', ['log' => $log->id]) }}">
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
                        @endforeach
                    </tbody>
                </table>
                {{ $logs->links() }}
            </div>
        </div>
    </div>

</x-app-layout>
