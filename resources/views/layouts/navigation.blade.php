<nav class="bg-white">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <x-application-logo class="block w-auto text-gray-800 fill-current h-9" />
                </div>
                <div class="hidden md:block">
                    <div class="flex items-baseline ml-10 space-x-4">
                        @auth
                            @hasanyrole('Admin|Operator')
                                <x-nav-link :href="route('index')" :active="request()->routeIs('index')">
                                    {{ __('index') }}
                                </x-nav-link>
                                <x-nav-link :href="route('products.index')" :active="request()->is('products')">
                                    {{ __('Products') }}
                                </x-nav-link>
                                <x-nav-link :href="route('organizations.index')" :active="request()->is('organizations')">
                                    {{ __('Organizations') }}
                                </x-nav-link>
                                <x-nav-link :href="route('users.index')" :active="request()->is('users')">
                                    {{ __('Users') }}
                                </x-nav-link>
                                <x-nav-link :href="route('tools.index')" :active="request()->is('tools')">
                                    {{ __('Tools') }}
                                </x-nav-link>
                            @endhasanyrole
                            @hasrole('Admin')
                                <x-nav-link :href="route('logs.index')" :active="request()->is('logs')">
                                    {{ __('Logs') }}
                                </x-nav-link>
                            @endhasrole
                            <!--  HIDDEN WHEN ADMIN   -->
                            @hasanyrole('Servicer|Organizer')
                                <x-nav-link :href="route('products.search')" :active="request()->is('product/search')">
                                    {{ __('Product search') }}
                                </x-nav-link>
                                <x-nav-link :href="route('products.myproducts')" :active="request()->is('product/myproducts')">
                                    {{ __('My products') }}
                                </x-nav-link>
                            @endhasanyrole
                            @hasrole('Organizer')
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
                <div class="flex items-center ml-4 md:ml-6">
                    {{--  @auth
                        <button
                            class="relative p-1 text-white rounded-full bg-primary-500 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                            type="button">
                            <span class="absolute -inset-1.5"></span>
                            <span class="sr-only">{{ __('View notifications') }}</span>
                            <x-svg.search />
                        </button>
                    @endauth --}}
                    <!-- Profile dropdown -->
                    <div class="relative ml-3" id="">
                        @auth
                            <div>
                                <button
                                    class="relative flex items-center max-w-xs text-sm bg-gray-800 rounded-full focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                                    id="user-menu-button" type="button" aria-expanded="false" aria-haspopup="true">
                                    <span class="absolute -inset-1.5"></span>
                                    <span class="sr-only">Open user menu</span>
                                    <img class="w-8 h-8 rounded-full"
                                        src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                                        alt="">
                                </button>
                            </div>

                            <div class="absolute right-0 z-10 hidden w-48 py-1 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-black ring-1 ring-opacity-5 focus:outline-none"
                                id="profile-dropdown" role="menu" aria-orientation="vertical"
                                aria-labelledby="user-menu-button" tabindex="-1">
                                <!-- Active: "bg-gray-100", Not Active: "" -->
                                <a class="block px-4 py-2 text-sm text-gray-700" id="user-menu-item-0"
                                    href="{{ route('profile.edit') }}" role="menuitem" tabindex="-1" wire:navigate>
                                    {{ __('Your Profile') }}
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    <a class="block px-4 py-2 text-sm text-gray-700" id="user-menu-item-2"
                                        href="{{ route('logout') }}" role="menuitem" tabindex="-1" wire:navigate
                                        onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                        @csrf
                                        <!-- Authentication -->
                                        {{ __('Log Out') }}

                                    </a>
                                </form>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const userMenuButton = document.getElementById('user-menu-button');
                    const profileDropdown = document.getElementById('profile-dropdown');

                    if (userMenuButton && profileDropdown) {
                        userMenuButton.addEventListener('click', function() {
                            profileDropdown.classList.toggle('hidden');
                        });

                        // Close dropdown when clicking outside
                        document.addEventListener('click', function(event) {
                            if (!userMenuButton.contains(event.target) && !profileDropdown.contains(event.target)) {
                                profileDropdown.classList.add('hidden');
                            }
                        });
                    }
                });
            </script>
            <div class="flex -mr-2 md:hidden">
                <!-- Mobile menu button -->
                <button
                    class="relative inline-flex items-center justify-center p-2 text-gray-400 bg-gray-800 rounded-md hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                    id="menu-toggle" type="button" aria-controls="mobile-menu" aria-expanded="false">
                    <span class="absolute -inset-0.5"></span>
                    <span class="sr-only">Open main menu</span>
                    <!-- Menu open: "hidden", Menu closed: "block" -->
                    <svg class="block w-6 h-6" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <!-- Menu open: "block", Menu closed: "hidden" -->
                    <svg class="hidden w-6 h-6" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
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
                @role('Servicer|Organizer')
                    <!-- Current: "bg-gray-900 text-white", Default: "text-gray-300 hover:bg-gray-700 hover:text-white" -->
                    <x-menu-item :href="route('products.search')" :active="request()->is('product/search')">
                        {{ __('product search') }}
                    </x-menu-item>
                    <x-menu-item :href="route('products.myproducts')" :active="request()->is('product/myproducts')">
                        {{ __('my products') }}
                    </x-menu-item>
                    @role('Organizer')
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
                        <img class="w-10 h-10 rounded-full"
                            src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                            alt="">
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium leading-none text-white">{{ Auth::user()->name }}</div>
                        <div class="text-sm font-medium leading-none text-gray-400">{{ Auth::user()->email }}</div>
                    </div>
                    <button
                        class="relative flex-shrink-0 p-1 ml-auto text-gray-400 bg-gray-800 rounded-full hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                        type="button">
                        <span class="absolute -inset-1.5"></span>
                        <span class="sr-only">View notifications</span>
                        <x-svg.search />
                    </button>
                </div>
                <div class="px-2 mt-3 space-y-1">
                    <a class="block px-3 py-2 text-base font-medium text-gray-400 rounded-md hover:bg-gray-700 hover:text-white"
                        href="{{ route('profile.edit') }}" wire:navigate>
                        {{ __('Your Profile') }}
                    </a>
                    <form method="GET" action="{{ route('logout') }}">
                        <a class="block px-3 py-2 text-base font-medium text-gray-400 rounded-md hover:bg-gray-700 hover:text-white"
                            id="user-menu-item-2" href="{{ route('logout') }}" role="menuitem" tabindex="-1" wire:navigate
                            onclick="event.preventDefault();
                                                this.closest('form').submit();">
                            @csrf
                            <!-- Authentication -->
                            {{ __('Log Out') }}
                        </a>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>
