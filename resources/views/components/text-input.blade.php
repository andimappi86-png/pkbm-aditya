@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-border bg-surface text-navy focus:border-primary focus:ring-primary rounded-xl shadow-sm']) }}>
