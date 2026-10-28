@props(['text' => 'Konten sementara'])

<span {{ $attributes->merge(['class' => 'temp-badge']) }}>
    <span class="temp-badge__dot" aria-hidden="true"></span>
    {{ $text }}
</span>
