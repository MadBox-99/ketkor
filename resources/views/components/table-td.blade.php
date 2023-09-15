@props(['to_String' => false])
<td {{ $attributes->merge(['class' => 'text-center self-center']) }}>
    @if (!empty($slot->__toString()))
        @if ($to_String)
            {{ __($slot->__toString()) }}
        @else
            {{ $slot }}
        @endif
    @else
        &nbsp;
    @endif
</td>
