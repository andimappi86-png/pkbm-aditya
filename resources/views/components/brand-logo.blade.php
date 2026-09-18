@props(['size' => 'md', 'withText' => true, 'subtitle' => false])

@php
$sizes = [
    'sm' => 'h-8 w-8',
    'md' => 'h-12 w-12',
    'lg' => 'h-16 w-16',
    'xl' => 'h-24 w-24',
];

$textSizes = [
    'sm' => 'text-sm',
    'md' => 'text-base',
    'lg' => 'text-xl',
    'xl' => 'text-2xl',
];

$sizeClass = $sizes[$size] ?? $sizes['md'];
$textSize = $textSizes[$size] ?? $textSizes['md'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo PKBM Aditya" class="{{ $sizeClass }} object-contain">
    
    @if($withText)
        <div class="flex flex-col">
            <span class="{{ $textSize }} font-bold text-primary-dark leading-tight">PKBM ADITYA</span>
            @if($subtitle)
                <span class="text-xs text-muted leading-tight">Kabupaten Mamuju</span>
            @endif
        </div>
    @endif
</div>
```