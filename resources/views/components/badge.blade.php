@props([
    'variant' => 'primary',
    'size' => 'md',
    'dot' => false,
])

@php
$sizeClasses = match($size) {
    'sm' => 'px-2 py-0.5 text-xs',
    default => 'px-2.5 py-1 text-xs',
};

$variantClasses = match($variant) {
    'primary' => 'bg-primary-soft text-primary-dark border-primary/20',
    'success' => 'bg-primary-soft text-primary-dark border-primary/20',
    'warning' => 'bg-background text-muted border-border',
    'danger' => 'bg-accent-soft text-accent border-accent/20',
    'neutral' => 'bg-muted/10 text-muted border-muted/20',
    default => 'bg-primary-soft text-primary-dark border-primary/20',
};

$dotClasses = match($variant) {
    'primary' => 'bg-primary',
    'success' => 'bg-primary',
    'warning' => 'bg-muted',
    'danger' => 'bg-accent',
    'neutral' => 'bg-muted',
    default => 'bg-primary',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 font-medium rounded-full border {$sizeClasses} {$variantClasses}"]) }}>
    @if ($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotClasses }}"></span>
    @endif
    {{ $slot }}
</span>
