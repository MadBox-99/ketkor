<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="resetPassword" class="flex flex-col gap-6">
        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
            <input wire:model="email" id="email" type="email" required autocomplete="email"
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
                {{ __('Reset password') }}
            </button>
        </div>
    </form>
</div>
