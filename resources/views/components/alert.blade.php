@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
$config = match($variant) {
    'success' => [
        'wrapper' => 'bg-primary-soft border-primary/30 text-primary-dark',
        'iconBg' => 'text-primary-dark',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
    ],
    'danger' => [
        'wrapper' => 'bg-accent-soft border-accent/30 text-accent',
        'iconBg' => 'text-accent',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
    ],
    default => [
        'wrapper' => 'bg-muted/10 border-muted/30 text-muted',
        'iconBg' => 'text-muted',
        'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
    ],
};
@endphp

<div
    @if ($dismissible)
        x-data="{ show: true }"
        x-show="show"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    @endif
    {{ $attributes->merge(['class' => 'flex items-start gap-3 p-4 rounded-xl border text-sm ' . $config['wrapper']]) }}
>
    <div class="flex-shrink-0 mt-0.5 {{ $config['iconBg'] }}">
        {!! $config['icon'] !!}
    </div>

    <div class="flex-1 min-w-0">
        @if ($title)
            <h4 class="font-semibold mb-1 text-sm">{{ $title }}</h4>
        @endif
        <div class="text-sm opacity-90 leading-relaxed">
            {{ $slot }}
        </div>
    </div>

    @if ($dismissible)
        <button
            type="button"
            @click="show = false"
            class="flex-shrink-0 ml-auto -mr-1 -mt-1 p-1 rounded-lg text-current opacity-60 hover:opacity-100 hover:bg-black/5 transition"
            aria-label="Tutup"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
