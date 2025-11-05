@extends('errors::layout')

@section('title', 'Az oldal lejárt')
@section('code', '419')
@section('message', 'Az oldal munkamenete lejárt. Kérjük, frissítse az oldalt és próbálja újra.')

@section('content')
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Ez általában akkor történik, ha túl sokáig nincs aktivitás az oldalon.
    </p>
@endsection
