@props(['active'])

@php
    $classes = $active ?? false
        ? 'rounded-md bg-primary-500 dark:bg-primary-600 px-3 py-2 text-sm font-medium text-white'
        : 'text-gray-700 dark:text-gray-300 rounded-md px-3 py-2 text-sm font-medium hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white';
@endphp

<a wire:navigate {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
