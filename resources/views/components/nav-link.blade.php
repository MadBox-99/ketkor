@props(['active'])

@php
    $classes = $active ?? false ? 'rounded-md bg-primary-500 px-3 py-2 text-sm font-medium text-white' : 'text-primary rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white';
@endphp

<a wire:navigate {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
