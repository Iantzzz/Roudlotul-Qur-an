@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'align' => 'left',
    'theme' => 'light',
    'temporary' => false,
])

<div {{ $attributes->merge(['class' => "section-heading section-heading--{$align} section-heading--{$theme}"]) }}>
    @if ($eyebrow)
        <span class="eyebrow">{{ $eyebrow }}</span>
    @endif

    <h2>{{ $title }}</h2>

    @if ($description)
        <p>{{ $description }}</p>
    @endif

    @if ($temporary)
        <x-temp-badge class="section-heading__badge" />
    @endif
</div>
