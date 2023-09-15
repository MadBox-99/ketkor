<th {{ $attributes->merge(['class' => 'my-5 py-1 text-center self-center']) }}>
    {{ __($slot->__toString()) }}
</th>
