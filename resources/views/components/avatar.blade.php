@props([
    'src' => null,
    'name' => 'User',
    'size' => 'md',
    'status' => null,
])

@php
$sizeClasses = match($size) {
    'xs' => 'w-7 h-7 text-xs',
    'sm' => 'w-8 h-8 text-xs',
    'lg' => 'w-14 h-14 text-base font-semibold',
    'xl' => 'w-20 h-20 text-xl font-bold',
    default => 'w-10 h-10 text-sm font-semibold',
};

// Generate initials from name (up to 2 characters)
$words = preg_split('/\s+/', trim($name));
$initials = '';
foreach (array_slice($words, 0, 2) as $w) {
    $initials .= mb_substr($w, 0, 1);
}
$initials = strtoupper($initials ?: 'U');

$statusClasses = match($status) {
    'online' => 'bg-primary',
    'busy' => 'bg-accent',
    'away' => 'bg-muted',
    default => 'bg-muted',
};
@endphp

<div class="relative inline-flex items-center justify-center flex-shrink-0">
    @if ($src)
        <img
            src="{{ $src }}"
            alt="{{ $name }}"
            {{ $attributes->merge(['class' => "{$sizeClasses} rounded-full object-cover border border-border"]) }}
        />
    @else
        <div {{ $attributes->merge(['class' => "{$sizeClasses} rounded-full bg-primary-soft text-primary-dark border border-primary/20 flex items-center justify-center select-none"]) }}>
            <span>{{ $initials }}</span>
        </div>
    @endif

    @if ($status)
        <span class="absolute bottom-0 right-0 block w-2.5 h-2.5 rounded-full ring-2 ring-white {{ $statusClasses }}"></span>
    @endif
</div>
