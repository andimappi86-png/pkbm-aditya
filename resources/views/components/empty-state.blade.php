@props([
    'title' => 'Belum ada data',
    'description' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-12 px-4 rounded-2xl border-2 border-dashed border-border bg-background/50 flex flex-col items-center justify-center']) }}>
    @if ($icon)
        <div class="w-14 h-14 rounded-2xl bg-surface border border-border shadow-card flex items-center justify-center text-muted mb-4">
            {!! $icon !!}
        </div>
    @else
        <div class="w-14 h-14 rounded-2xl bg-surface border border-border shadow-card flex items-center justify-center text-muted mb-4">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        </div>
    @endif

    <h3 class="text-base font-semibold text-navy">{{ $title }}</h3>

    @if ($description)
        <p class="text-sm text-muted mt-1 max-w-sm">{{ $description }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mt-5">
            {{ $slot }}
        </div>
    @endif
</div>
