<div class="mt-4 flex flex-col gap-6">
    <p class="text-center text-gray-600">
        {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <p class="text-center font-medium text-green-600 dark:text-green-400">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </p>
    @endif

    <div class="flex flex-col items-center justify-between space-y-3">
        <button wire:click="sendVerification"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            {{ __('Resend verification email') }}
        </button>

        <button wire:click="logout" class="text-sm text-indigo-600 hover:text-indigo-500 cursor-pointer">
            {{ __('Log out') }}
        </button>
    </div>
</div>
