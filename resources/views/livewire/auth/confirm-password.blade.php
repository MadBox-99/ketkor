<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Confirm password')" :description="__('This is a secure area of the application. Please confirm your password before continuing.')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="confirmPassword" class="flex flex-col gap-6">
        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
            <input wire:model="password" id="password" type="password" required autocomplete="new-password"
                placeholder="{{ __('Password') }}"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
        </div>

        <button type="submit"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ __('Confirm') }}
        </button>
    </form>
</div>
