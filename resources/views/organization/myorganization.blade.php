<x-app-layout>
    <!-- Page Heading -->
    <x-slot name="header">
        <div class="mb-4 flex items-center justify-between font-bold">
            <div class="basis-auto">
                <h1 class="mx-4 px-20 text-primary">{{ __('Organization edit') }}</h1>
            </div>
        </div>
    </x-slot>
    {{-- Alert Messages --}}
    <x-alert />
    {{-- Page content --}}

    <div class="my-12">
        <div class="border-b flex justify-center border-gray-900/10 pb-12">
            <div class="flex w-full max-w-7xl flex-wrap justify-center text-center">
                <div name='form_field'>
                    <form method="POST"
                        action="{{ route('organizations.myorganizationupdate', ['organization' => $organization->id]) }}"
                        class="mb-4 flex basis-full flex-wrap justify-center rounded bg-white px-8 pb-8 pt-6 shadow-md">
                        @csrf
                        @method('PUT')
                        <div class="flex flex-wrap">
                            <div class="basis-full text-left">
                                <div class="flex flex-wrap">
                                    <x-create-input-text name="name" class="basis-full"
                                        headText="Organization name">{{ $organization->name }}</x-create-input-text>
                                    <x-create-input-text name="city" class="basis-full"
                                        headText="City">{{ $organization->city }}</x-create-input-text>
                                    <x-create-input-text name="address" class="basis-full"
                                        headText="Address">{{ $organization->address }}</x-create-input-text>
                                    <x-create-input-text name="tax_number" class="basis-full"
                                        headText="Tax number">{{ $organization->tax_number }}</x-create-input-text>
                                    <x-create-input-text name="zip" class="basis-full"
                                        headText="zip">{{ $organization->zip }}</x-create-input-text>
                                </div>
                            </div>
                            <div class="basis-full text-left">
                                {{-- Save Button --}}
                                <button type="submit"
                                    class="my-10 rounded bg-blue-500 px-4 py-2 text-center font-bold text-white hover:bg-blue-700 focus:outline-none sm:inline-block">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @foreach ($organization->users as $user)
                    <div
                        class="mb-4 flex w-full flex-wrap justify-center rounded bg-white px-8 pb-8 pt-6 text-xl shadow-md">
                        {{-- row 1 --}}
                        <div class="basis-full">
                            <div class="flex h-24 w-full content-between items-center">
                                <div class="basis-2/12">
                                    {{ $user->name }}
                                </div>
                                <div class="basis-7/12">
                                    &nbsp;
                                </div>
                                <div class="basis-3/12 rounded-md bg-primary-400 text-center">
                                    <a
                                        href="{{ route('organizations.removeUserFromOrganization', ['user' => $user]) }}">{{ __('user delete') }}</a>
                                </div>
                            </div>
                        </div>
                        {{-- row 2 --}}
                        <div class="m-auto my-1 basis-full self-auto py-1 text-left odd:bg-white even:bg-gray-200">
                            {{-- container --}}
                            <div class="flex flex-wrap">
                                {{-- row 1 --}}
                                <div class="h-12 basis-full">
                                    <div class="flex h-12 flex-nowrap items-center text-center">
                                        <div class="xs:basis-1/4 sm:block sm:basis-3/12 md:basis-2/12">
                                            {{ __('Serial number') }}
                                        </div>
                                        <div class="xs:hidden sm:hidden sm:basis-1/12 md:block md:basis-2/12">
                                            {{ __('City') }}
                                        </div>
                                        <div class="xs:hidden sm:hidden sm:basis-1/12 md:block md:basis-2/12">
                                            {{ __('Purchase place') }}
                                        </div>
                                        <div class="xs:w-basis-2/4 sm:block sm:basis-4/12 md:basis-2/12">
                                            {{ __('Tool name') }}
                                        </div>
                                        <div class="xs:hidden sm:block sm:basis-3/12 md:basis-2/12">
                                            {{ __('Warrantee') }}
                                        </div>
                                        <div class="xs:basis-1/4 xs:text-base sm:block sm:basis-2/12 md:basis-2/12">
                                            {{ __('action') }}
                                        </div>
                                    </div>
                                </div>
                                {{-- row 2 --}}
                                @forelse ($user->products as $product)
                                    <div class="h-20 basis-full bg-primary-200 py-5 text-center odd:bg-gray-400">
                                        <div class="flex h-20 flex-nowrap items-center sm:h-12">
                                            <div class="xs:basis-1/4 sm:block sm:basis-3/12 md:basis-2/12">
                                                {{ $product->serial_number }}
                                            </div>
                                            <div class="xs:hidden sm:hidden sm:basis-1/12 md:block md:basis-2/12">
                                                {{ $product->city }}
                                            </div>
                                            <div class="xs:hidden sm:hidden sm:basis-1/12 md:block md:basis-2/12">
                                                {{ $product->purchase_place }}
                                            </div>
                                            <div class="xs:w-basis-2/4 sm:block sm:basis-4/12 md:basis-2/12">
                                                {{ $product->tool->name }}
                                            </div>
                                            <div class="xs:hidden sm:block sm:basis-3/12 md:basis-2/12">
                                                {{ $product->serializeDate($product->warrantee_date) }}
                                            </div>
                                            <div class="xs:basis-1/4 xs:text-base sm:block sm:basis-2/12 md:basis-2/12">
                                                <a wire:navigate
                                                    href="{{ route('organizations.detach', [
                                                        'organization' => $organization->id,
                                                        'product' => $product->id,
                                                        'user' => $user->id,
                                                    ]) }}">{{ __('Remove product from user') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="h-12 basis-full bg-primary-200 text-center">
                                        <div class="flex h-12 flex-nowrap items-center">
                                            <div class="basis-full">
                                                {{ _('no product found') }}
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
