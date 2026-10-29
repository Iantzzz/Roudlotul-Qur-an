@extends('layouts.public')

@section('title', $post['title'] . " | Roudlotul Qur'an")
@section('description', $post['excerpt'])

@section('content')
    <x-page-hero
        :title="$post['title']"
        :description="$post['excerpt']"
        eyebrow="Berita"
        image="quran_manuscript"
        parentLabel="Berita"
        parentRoute="berita"
    />

    <section class="section">
        <div class="container article-layout">
            <article class="article">
                <div class="article-meta">
                    <span class="news-card__category">{{ $post['category'] }}</span>
                    <span>{{ $post['date'] }}</span>
                    <span>{{ $post['author'] }}</span>
                    @if ($post['temporary'] ?? false)
                        <x-temp-badge text="Konten sementara" />
                    @endif
                </div>

                <x-site-image :name="$post['image']" class="article-image" loading="eager" />

                <div class="article-copy">
                    @foreach (preg_split('/\R{2,}/', trim($post['body'])) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>

                <div class="article-note">
                    <p>
                        Berita ini dirangkum dari dokumentasi resmi ribath di akun
                        media sosial Roudlotul Qur'an.
                    </p>
                </div>

                <div class="button-row">
                    <a class="btn btn-outline" href="{{ route('berita') }}">
                        Kembali ke Berita
                    </a>
                </div>
            </article>
        </div>
    </section>

    <section class="section section--cream">
        <div class="container">
            <div class="section-toolbar">
                <x-section-heading
                    eyebrow="Berita Terkait"
                    title="Bacaan lain dari halaman berita."
                    description="Kartu berikut dipilih berdasarkan kedekatan kategori."
                />
            </div>

            <div class="news-grid">
                @foreach ($related as $relatedPost)
                    <div data-reveal>
                        <x-news-card :post="$relatedPost" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
