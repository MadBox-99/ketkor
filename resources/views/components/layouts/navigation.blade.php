<nav class="bg-white dark:bg-gray-900">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <x-application-logo class="block w-auto text-gray-800 fill-current h-16" />
                </div>
                <div class="hidden md:block">
                    <div class="flex items-baseline ml-10 space-x-4">
                        @auth

                            <!--  HIDDEN WHEN ADMIN   -->
                            @hasanyrole('Servicer|Organizer|Admin|Super Admin')
                                <x-nav-link :href="route('products.search')" :active="request()->is('product/search')">
                                    {{ __('Product search') }}
                                </x-nav-link>
                                <x-nav-link :href="route('products.myproducts')" :active="request()->is('product/myproducts')">
                                    {{ __('My products') }}
                                </x-nav-link>
                            @endhasanyrole
                            @hasrole('Organizer|Admin|Super Admin')
                                <x-nav-link :href="route('organizations.myorganization')" :active="request()->is('organization/myorganization')">
                                    {{ __('My myorganization') }}
                                </x-nav-link>
                            @endhasrole
                        @else
                            <x-nav-link :href="route('login')" :active="request()->is('login')">
                                {{ __('Login') }}
                            </x-nav-link>
                        @endauth
                    </div>
                </div>
            </div>

            <div class="hidden md:block">
                <div class="flex items-center ml-4 space-x-3 md:ml-6">
                    <!-- Dark mode toggle -->
                    <x-dark-mode-toggle />

                    <!-- Profile dropdown -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        @auth
                            <div>
                                <button @click="open = !open"
                                    class="relative flex items-center rounded-full transition hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                                    type="button" :aria-expanded="open" aria-haspopup="true">
                                    <span class="sr-only">{{ __('Open user menu') }}</span>
                                    <x-user-avatar :user="Auth::user()" size="md" />
                                </button>
                            </div>

                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 z-50 w-56 py-1 mt-2 origin-top-right bg-white rounded-lg shadow-xl dark:bg-gray-800 ring-1 ring-black ring-opacity-5 dark:ring-gray-700 focus:outline-none"
                                role="menu" aria-orientation="vertical" style="display: none;" @click="open = false">
                                <!-- User Info Header -->
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ Auth::user()->name }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate dark:text-gray-400">{{ Auth::user()->email }}
                                    </p>
                                </div>

                                <!-- Menu Items -->
                                <div class="py-1">
                                    <a class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 transition dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        href="{{ route('profile.edit') }}" role="menuitem" wire:navigate>
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ __('Your Profile') }}
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="flex w-full items-center gap-2 px-4 py-2 text-sm text-left text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                            role="menuitem">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            {{ __('Log Out') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>

            <div class="flex -mr-2 md:hidden">
                <!-- Mobile menu button -->
                <button
                    class="relative inline-flex items-center justify-center p-2 text-gray-500 rounded-md hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                    id="menu-toggle" type="button" aria-controls="mobile-menu" aria-expanded="false">
                    <span class="absolute -inset-0.5"></span>
                    <span class="sr-only">{{ __('Open main menu') }}</span>
                    <!-- Menu open: "hidden", Menu closed: "block" -->
                    <svg class="block w-8 h-8" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <!-- Menu open: "block", Menu closed: "hidden" -->
                    <svg class="hidden w-8 h-8" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div class="hidden bg-gray-800 md:hidden" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            @auth
                @role('Servicer|Organizer|Admin|Super Admin')
                    <!-- Current: "bg-gray-900 text-white", Default: "text-gray-300 hover:bg-gray-700 hover:text-white" -->
                    <x-menu-item :href="route('products.search')" :active="request()->is('product/search')">
                        {{ __('product search') }}
                    </x-menu-item>
                    <x-menu-item :href="route('products.myproducts')" :active="request()->is('product/myproducts')">
                        {{ __('my products') }}
                    </x-menu-item>
                    @role('Organizer|Admin|Super Admin')
                        <x-menu-item :href="route('organizations.myorganization')" :active="request()->is('organization/myorganization')">
                            {{ __('my myorganization') }}
                        </x-menu-item>
                    @endrole
                @endrole
            @else
                <x-menu-item :href="route('login')" :active="request()->is('login')">
                    {{ __('login') }}
                </x-menu-item>
            @endauth
        </div>
        @auth
            <div class="pt-4 pb-3 border-t border-gray-700">
                <div class="flex items-center px-5">
                    <div class="flex-shrink-0">
                        <x-user-avatar :user="Auth::user()" size="lg" />
                    </div>
                    <div class="ml-3 min-w-0 flex-1">
                        <div class="truncate text-base font-medium leading-none text-white">{{ Auth::user()->name }}</div>
                        <div class="mt-1 truncate text-sm font-medium leading-none text-gray-400">{{ Auth::user()->email }}
                        </div>
                    </div>
                    <div class="flex items-center ml-auto space-x-2">
                        <!-- Dark mode toggle for mobile -->
                        <x-dark-mode-toggle-mobile />

                        <button
                            class="relative flex-shrink-0 p-1 text-gray-400 bg-gray-800 rounded-full hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                            type="button">
                            <span class="absolute -inset-1.5"></span>
                            <span class="sr-only">{{ __('View notifications') }}</span>
                            <x-svg.search />
                        </button>
                    </div>
                </div>
                <div class="px-2 mt-3 space-y-1">
                    <a class="block px-3 py-2 text-base font-medium text-gray-400 rounded-md hover:bg-gray-700 hover:text-white"
                        href="{{ route('profile.edit') }}" wire:navigate>
                        {{ __('Your Profile') }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="block w-full px-3 py-2 text-base font-medium text-left text-gray-400 rounded-md hover:bg-gray-700 hover:text-white"
                            role="menuitem" tabindex="-1">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>
