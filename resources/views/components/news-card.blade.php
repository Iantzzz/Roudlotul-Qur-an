@props(['post'])

<article class="news-card">
    <a
        class="news-card__media"
        href="{{ route('berita.show', $post['slug']) }}"
        tabindex="-1"
        aria-hidden="true"
    >
        <x-site-image :name="$post['image']" class="news-card__image" />
    </a>

    <div class="news-card__body">
        <div class="news-card__meta">
            <span class="news-card__category">{{ $post['category'] }}</span>
            <span>{{ $post['date'] }}</span>
        </div>

        <h3>
            <a href="{{ route('berita.show', $post['slug']) }}">{{ $post['title'] }}</a>
        </h3>

        <p>{{ $post['excerpt'] }}</p>

        <div class="news-card__footer">
            @if ($post['temporary'] ?? false)
                <x-temp-badge text="Contoh" />
            @endif
            <a class="text-link" href="{{ route('berita.show', $post['slug']) }}">
                Baca selengkapnya
                <x-site-icon name="arrow-right" :size="17" />
            </a>
        </div>
    </div>
</article>
