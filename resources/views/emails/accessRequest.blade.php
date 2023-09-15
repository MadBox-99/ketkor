<x-mail::message>
    <x-mail::panel>
        {{ __('You have been granted access to view the content. Click the link below:') }}
    </x-mail::panel>
    <x-mail::table>
        <x-mail::button :url="route('accestokens.activateAccessToken', ['token' => $token])" color="success">
            {{ __('Access Content') }}
        </x-mail::button>
    </x-mail::table>
</x-mail::message>
