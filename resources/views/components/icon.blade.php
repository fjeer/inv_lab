@props([
    'size' => 'md',
    'variant' => 'default',
    'class' => '',
])

@php
    $sizes = [
        'sm' => 'w-4 h-4',
        'md' => 'w-5 h-5',
        'lg' => 'w-6 h-6',
        'xl' => 'w-8 h-8',
    ];

    $variants = [
        'default' => '',
        'gradient' => 'text-white',
        'glow' => 'icon-glow',
        'float' => 'icon-float',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $variantClass = $variants[$variant] ?? '';
@endphp

<div class="icon-container inline-flex items-center justify-center {{ $variant === 'gradient' ? 'bg-gradient-to-br from-primary to-primary-dark shadow-lg shadow-primary/25' : '' }} {{ $variant === 'glow' ? 'drop-shadow-lg' : '' }} {{ $class }}">
    <svg class="{{ $sizeClass }} shrink-0 icon-premium {{ $variantClass }}@if($variant === 'float') icon-float @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24" {{ $attributes }}>
        {{ $slot }}
    </svg>
</div>
