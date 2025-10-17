@props(['active'])

@php
    $classes = $active ?? false
        ? 'block rounded-md bg-gray-900 dark:bg-gray-700 px-3 py-2 text-base font-medium text-white'
        : 'block rounded-md px-3 py-2 text-base font-medium text-gray-300 dark:text-gray-400 hover:bg-gray-700 dark:hover:bg-gray-600 hover:text-white';
@endphp

<a wire:navigate {{ $attributes->merge(['class' => $classes]) }} aria-current="page">{{ $slot }}</a>
