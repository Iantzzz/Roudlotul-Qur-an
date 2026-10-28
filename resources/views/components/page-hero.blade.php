@props([
    'eyebrow' => 'Roudlotul Qur’an',
    'title',
    'description' => null,
    'image' => 'mosque_light',
    'parentLabel' => null,
    'parentRoute' => null,
])

<section class="page-hero">
    <x-site-image :name="$image" class="page-hero__image" loading="eager" fetchpriority="high" />

    <div class="page-hero__overlay" aria-hidden="true"></div>

    <div class="container page-hero__content">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Beranda</a>

            @if ($parentLabel && $parentRoute)
                <span aria-hidden="true">/</span>
                <a href="{{ route($parentRoute) }}">{{ $parentLabel }}</a>
            @endif

            <span aria-hidden="true">/</span>
            <span aria-current="page">{{ $title }}</span>
        </nav>

        <span class="eyebrow eyebrow--gold">{{ $eyebrow }}</span>
        <h1>{{ $title }}</h1>

        @if ($description)
            <p>{{ $description }}</p>
        @endif
    </div>
</section>
