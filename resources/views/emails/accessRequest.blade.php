@component('mail::message')

    {{ __('Activation button make: ') }}{{ $name }}{{ __(' access to private datas.') }}
    <br />
    @component('mail::button', ['url' => route('accestokens.activateAccessToken', ['token' => $token])])
        {{ __('Access Content') }}
    @endcomponent

@endcomponent
