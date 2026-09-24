@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null,
    'badge' => null,
    'variant' => 'default',
])

@php
$iconWrapperClasses = match($variant) {
    'primary' => 'bg-primary-soft text-primary-dark',
    'accent' => 'bg-red-50 text-accent',
    'success' => 'bg-primary-soft text-primary-dark',
    'warning' => 'bg-muted/10 text-muted',
    default => 'bg-primary-soft text-primary-dark',
};
@endphp

<div {{ $attributes->merge(['class' => 'bg-surface p-6 rounded-2xl border border-border shadow-card hover:shadow-soft transition-all duration-200 flex flex-col justify-between']) }}>
    <div class="flex items-center justify-between gap-4 mb-3">
        <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ $title }}</span>
        @if ($badge)
            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-muted/10 text-muted">{{ $badge }}</span>
        @endif
        @if ($icon)
            <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $iconWrapperClasses }}">
                @if(str_contains($icon, '<svg') || str_contains($icon, '<path'))
                    {!! $icon !!}
                @else
                    <x-icon name="{{ $icon }}" class="w-6 h-6" />
                @endif
            </div>
        @endif
    </div>

    <div>
        <div class="text-2xl lg:text-3xl font-bold text-navy tracking-tight">{{ $value }}</div>
        @if ($subtitle)
            <p class="text-xs text-muted mt-1 flex items-center gap-1">{{ $subtitle }}</p>
        @endif
    </div>
</div>
