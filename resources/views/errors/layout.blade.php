<x-layouts.app>
    <div class="flex min-h-[calc(100vh-4rem)] items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md text-center">
            <!-- Error Code -->
            <div class="mb-8">
                <h1 class="text-9xl font-extrabold text-gray-200 dark:text-gray-700">
                    @yield('code')
                </h1>
            </div>

            <!-- Error Title & Message -->
            <div class="mb-8">
                <h2 class="mb-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                    @yield('title')
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    @yield('message')
                </p>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-4 sm:flex-row sm:justify-center">
                <a href="{{ url('/') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 dark:bg-blue-500 dark:hover:bg-blue-400">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Vissza a főoldalra
                </a>

                <button onclick="history.back()"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Vissza
                </button>
            </div>

            <!-- Additional Content -->
            @hasSection('content')
                <div class="mt-8 rounded-lg bg-gray-50 p-6 dark:bg-gray-800">
                    @yield('content')
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
