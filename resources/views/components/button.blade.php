@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm active:scale-[0.99]';

$sizeClasses = match($size) {
    'sm' => 'px-3 py-1.5 text-xs gap-1.5',
    'lg' => 'px-6 py-3 text-base gap-2.5 shadow-soft',
    default => 'px-4 py-2.5 text-sm gap-2',
};

$variantClasses = match($variant) {
    'primary' => 'bg-primary hover:bg-primary-dark text-white shadow-soft focus:ring-primary',
    'dark' => 'bg-primary-dark hover:bg-primary-dark/90 text-white shadow-soft focus:ring-primary-dark',
    'secondary' => 'bg-surface hover:bg-background text-navy border border-border focus:ring-primary',
    'danger' => 'bg-accent hover:bg-accent-dark text-white shadow-soft focus:ring-accent',
    'danger-light' => 'bg-accent-soft hover:bg-red-50 text-accent border border-accent/20 focus:ring-accent',
    'outline' => 'border border-border bg-surface hover:bg-background text-navy focus:ring-primary',
    'ghost' => 'text-muted hover:text-navy hover:bg-muted/10 focus:ring-primary shadow-none',
    default => 'bg-primary hover:bg-primary-dark text-white shadow-soft focus:ring-primary',
};

$classes = "{$baseClasses} {$sizeClasses} {$variantClasses}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
