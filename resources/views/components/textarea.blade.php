@props([
    'disabled' => false,
    'label' => null,
    'name' => null,
    'rows' => 3,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'hint' => null,
    'error' => null,
])

@php
$id = $attributes->get('id') ?? $name ?? 'textarea-' . uniqid();
$hasError = $error || ($name && $errors->has($name));
$errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-navy mb-1.5">
            {{ $label }}
            @if ($required)
                <span class="text-accent">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-xl">
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder="{{ $placeholder }}"
            {{ $disabled ? 'disabled' : '' }}
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge([
                'class' => 'w-full px-4 py-2.5 rounded-xl border text-sm transition duration-150 ease-in-out ' .
                ($hasError
                    ? 'border-accent text-accent focus:border-accent focus:ring focus:ring-accent/20 '
                    : 'border-border bg-surface text-navy focus:border-primary focus:ring focus:ring-primary/20 ') .
                ($disabled ? 'bg-background cursor-not-allowed opacity-75' : '')
            ]) }}
        >{{ old($name, $value ?? $slot) }}</textarea>
    </div>

    @if ($hint && !$hasError)
        <p class="mt-1.5 text-xs text-muted">{{ $hint }}</p>
    @endif

    @if ($hasError)
        <p class="mt-1.5 text-xs text-accent flex items-center gap-1 font-medium">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ $errorMessage }}</span>
        </p>
    @endif
</div>
