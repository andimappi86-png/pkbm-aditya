@props([
    'items' => [],
])

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'flex items-center text-xs sm:text-sm font-medium text-muted']) }}>
    <ol class="inline-flex items-center space-x-1.5 md:space-x-2">
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-muted hover:text-primary transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>
        </li>

        @foreach ($items as $item)
            <li>
                <div class="flex items-center">
                    <svg class="w-3.5 h-3.5 text-slate-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>

                    @if (!empty($item['url']) && !$loop->last)
                        <a href="{{ $item['url'] }}" class="text-muted hover:text-primary transition-colors">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="text-navy font-semibold" aria-current="page">
                            {{ $item['label'] }}
                        </span>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</nav>
