<td
    {{ $attributes->merge(['class' => 'border-b border-slate-100 dark:border-slate-700 text-slate-500 dark:text-slate-400 p-4 pl-8']) }}>
    {{ __($slot->__toString()) }}
</td>
