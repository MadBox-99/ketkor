@extends('errors::layout')

@section('title', 'Túl sok kérés')
@section('code', '429')
@section('message', 'Túl sok kérést küldött rövid időn belül. Kérjük, várjon egy kicsit és próbálja újra.')

@section('content')
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Ez a korlátozás azért van, hogy megvédjük a szerverünket a túlterheléstől.
    </p>
@endsection
