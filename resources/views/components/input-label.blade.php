@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-navy']) }}>
    {{ $value ?? $slot }}
</label>
