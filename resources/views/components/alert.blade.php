{{-- Message --}}
@if (Session::has('success'))
    <div class="mb-3 hidden w-full items-center rounded-lg bg-success-400 px-6 py-5 text-base text-warning-800 data-[te-alert-show]:inline-flex"
        data-te-alert-init data-te-alert-show role="alert">
        <strong class="mr-1"> {{ __('Success!') }} </strong> {{ session('success') }}
        <button
            class="ml-auto box-content rounded-none border-none p-1 text-warning-900 opacity-50 hover:text-warning-900 hover:no-underline hover:opacity-75 focus:opacity-100 focus:shadow-none focus:outline-none"
            data-te-alert-dismiss type="button" aria-label="Close">
            <span
                class="w-[1em] focus:opacity-100 disabled:pointer-events-none disabled:select-none disabled:opacity-25 [&.disabled]:pointer-events-none [&.disabled]:select-none [&.disabled]:opacity-25">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z"
                        clip-rule="evenodd" />
                </svg>
            </span>
        </button>
    </div>
@endif

@if (Session::has('error'))
    <div class="mb-3 hidden w-full items-center rounded-lg bg-warning-100 px-6 py-5 text-base text-danger-700 data-[te-alert-show]:inline-flex"
        data-te-alert-init data-te-alert-show role="alert">
        <strong class="mr-1"> {{ __('Error!') }} </strong>
        {{ __('You should check in on some of those fields below.') }}
        {{ session('error') }}
        <button
            class="ml-auto box-content rounded-none border-none p-1 text-danger-900 opacity-50 hover:text-warning-900 hover:no-underline hover:opacity-75 focus:opacity-100 focus:shadow-none focus:outline-none"
            data-te-alert-dismiss type="button" aria-label="Close">
            <span
                class="w-[1em] focus:opacity-100 disabled:pointer-events-none disabled:select-none disabled:opacity-25 [&.disabled]:pointer-events-none [&.disabled]:select-none [&.disabled]:opacity-25">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z"
                        clip-rule="evenodd" />
                </svg>
            </span>
        </button>
    </div>
@endif
@if (Session::has('status'))
    <div class="mb-3 hidden w-full items-center rounded-lg bg-warning-100 px-6 py-5 text-base text-danger-700 data-[te-alert-show]:inline-flex"
        data-te-alert-init data-te-alert-show role="alert">
        <strong class="mr-1"> {{ __(session('status')) }} </strong>
        {{ session('status') }}
        <button
            class="ml-auto box-content rounded-none border-none p-1 text-danger-900 opacity-50 hover:text-warning-900 hover:no-underline hover:opacity-75 focus:opacity-100 focus:shadow-none focus:outline-none"
            data-te-alert-dismiss type="button" aria-label="Close">
            <span
                class="w-[1em] focus:opacity-100 disabled:pointer-events-none disabled:select-none disabled:opacity-25 [&.disabled]:pointer-events-none [&.disabled]:select-none [&.disabled]:opacity-25">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z"
                        clip-rule="evenodd" />
                </svg>
            </span>
        </button>
    </div>
@endif
