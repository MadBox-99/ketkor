<x-app-layout>
    <div class="">

        <!-- Page Heading -->
        <x-slot name="header">
            <x-button-style-link text="Edit tool" route="users.index">
                Back
            </x-button-style-link>
        </x-slot>
        {{-- Alert Messages --}}
        <x-alert />
        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <div class="max-w-xl">
                        <section>
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">
                                    {{ __('Product Information') }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __("Update product's informations.") }}
                                </p>
                            </header>
                            <form class="mt-6 space-y-6" method="POST"
                                action="{{ route('users.update', ['user' => $user->id]) }}">
                                @csrf
                                @method('PUT')
                                <div class="mb-4">
                                    <x-create-input-text name="name" headText="User name">
                                        {{ $user->name }}
                                    </x-create-input-text>
                                    <x-create-input-text name="email" headText="Email">
                                        {{ $user->email }}
                                    </x-create-input-text>
                                    <x-select-input name="organization" headText="Organization">
                                        <option value="">{{ __('Remove from organization.') }}</option>
                                        @foreach ($organizations as $organization)
                                            <x-select-input-option :value="$organization->id" :selected="$user->organization_id == $organization->id ? true : false">
                                                {{ $organization->name }}
                                            </x-select-input-option>
                                        @endforeach
                                    </x-select-input>
                                    <x-select-input name="role" headText="Role">
                                        @foreach ($roles as $role)
                                            <x-select-input-option :value="$role->name" :selected="$role->id == $user->roles->first()->id ? true : false">
                                                {{ $role->name }}
                                            </x-select-input-option>
                                        @endforeach
                                    </x-select-input>
                                </div>
                                {{-- Save Button --}}
                                <button
                                    class="rounded bg-blue-500 px-4 py-2 text-center font-bold text-white hover:bg-blue-700 focus:outline-none sm:inline-block"
                                    type="submit">
                                    {{ __('Save') }}
                                </button>

                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
