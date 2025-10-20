@props(['user', 'size' => 'md'])

@php
    $sizeClasses = [
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-10 h-10 text-base',
        'lg' => 'w-12 h-12 text-lg',
    ];

    $class = $sizeClasses[$size] ?? $sizeClasses['md'];

    // Get user initials
    $nameParts = explode(' ', $user->name);
    $initials = '';
    if (count($nameParts) >= 2) {
        $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
    } else {
        $initials = strtoupper(substr($user->name, 0, 2));
    }

    // Generate a consistent color based on user name
    $colors = [
        'bg-blue-500',
        'bg-green-500',
        'bg-purple-500',
        'bg-pink-500',
        'bg-indigo-500',
        'bg-yellow-500',
        'bg-red-500',
        'bg-teal-500',
    ];

    $colorIndex = abs(crc32($user->name)) % count($colors);
    $bgColor = $colors[$colorIndex];
@endphp

<div class="relative inline-flex items-center justify-center {{ $class }} {{ $bgColor }} rounded-full font-semibold text-white ring-2 ring-white dark:ring-gray-700">
    {{ $initials }}
</div>
