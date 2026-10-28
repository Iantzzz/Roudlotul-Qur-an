@props([
    'name',
    'alt' => null,
    'loading' => 'lazy',
    'fetchpriority' => 'auto',
    'width' => null,
    'height' => null,
])

@php
    $image = config("site.images.{$name}");
    $imageAlt = $alt ?? ($image['alt'] ?? 'Foto sementara Roudlotul Qur’an');
    $imageWidth = $width ?? ($image['width'] ?? null);
    $imageHeight = $height ?? ($image['height'] ?? null);
@endphp

@if ($image)
    <img
        src="{{ asset($image['path']) }}"
        alt="{{ $imageAlt }}"
        loading="{{ $loading }}"
        decoding="async"
        @if ($fetchpriority !== 'auto') fetchpriority="{{ $fetchpriority }}" @endif
        @if ($imageWidth) width="{{ $imageWidth }}" @endif
        @if ($imageHeight) height="{{ $imageHeight }}" @endif
        data-temporary-image="true"
        {{ $attributes->merge(['class' => 'site-image']) }}
    >
@endif
