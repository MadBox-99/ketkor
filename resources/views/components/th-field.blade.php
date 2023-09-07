<th
    {{ $attributes->merge(['class' => 'border-b dark:border-slate-600 text-slate-400 dark:text-slate-200 p-4 pb-3 pl-8 pt-0 text-left font-medium']) }}>
    {{ __($slot->__toString()) }}
</th>
