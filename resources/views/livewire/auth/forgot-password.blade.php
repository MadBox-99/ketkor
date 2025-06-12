 <div class="flex flex-col gap-6">
     <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

     <!-- Session Status -->
     <x-auth-session-status class="text-center" :status="session('status')" />

     <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
         <!-- Email Address -->
         <div>
             <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email Address') }}</label>
             <input wire:model="email" id="email" type="email" required autofocus placeholder="email@example.com"
                 class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
         </div>

         <button type="submit"
             class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
             {{ __('Email password reset link') }}
         </button>
     </form>

     <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
         {{ __('Or, return to') }}
         <a href="{{ route('login') }}" wire:navigate
             class="text-indigo-600 hover:text-indigo-500">{{ __('log in') }}</a>
     </div>
 </div>
