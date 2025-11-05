@extends('errors::layout')

@section('title', 'A szolgáltatás nem elérhető')
@section('code', '503')
@section('message', 'Az oldal jelenleg karbantartás alatt áll. Kérjük, látogasson vissza később.')

@section('content')
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Dolgozunk azon, hogy minél hamarabb visszaállítsuk a szolgáltatást.
    </p>
@endsection
