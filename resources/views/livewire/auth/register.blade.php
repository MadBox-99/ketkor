<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
            <input wire:model="name" id="name" type="text" required autofocus autocomplete="name"
                placeholder="{{ __('Full name') }}"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email address') }}</label>
            <input wire:model="email" id="email" type="email" required autocomplete="email"
                placeholder="email@example.com"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
            <input wire:model="password" id="password" type="password" required autocomplete="new-password"
                placeholder="{{ __('Password') }}"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation"
                class="block text-sm font-medium text-gray-700">{{ __('Confirm password') }}</label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password" required
                autocomplete="new-password" placeholder="{{ __('Confirm password') }}"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
        </div>

        <div class="flex items-center justify-end">
            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Create account') }}
            </button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('Already have an account?') }}
        <a href="{{ route('login') }}" wire:navigate
            class="text-indigo-600 hover:text-indigo-500">{{ __('Log in') }}</a>
    </div>
</div>
