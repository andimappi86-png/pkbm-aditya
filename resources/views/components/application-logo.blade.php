@props(['class' => 'h-12 w-auto'])

<img {{ $attributes->merge(['class' => $class]) }} src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo PKBM Aditya Kabupaten Mamuju" />
