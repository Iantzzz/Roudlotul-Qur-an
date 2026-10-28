@extends('layouts.public')

@section('title', "Berita & Pengumuman | Roudlotul Qur'an")
@section('description', "Daftar berita dan pengumuman contoh Roudlotul Qur'an. Seluruh isi masih sementara dan belum menjadi informasi resmi.")

@section('content')
    <x-page-hero
        title="Berita & Pengumuman"
        eyebrow="Kabar Asrama"
        description="Daftar berita dan pengumuman disiapkan untuk menampilkan informasi resmi pada tahap berikutnya."
        image="quran_manuscript"
    />

    <section class="section">
        <div class="container">
            <x-section-heading
                eyebrow="Berita Terbaru"
                title="Kabar yang akan hadir dari Roudlotul Qur’an."
                description="Seluruh kartu pada halaman ini masih berupa contoh. Judul, tanggal, penulis, dan isi resmi akan menggantikan data sementara."
                :temporary="true"
            />

            <nav class="news-filter" aria-label="Filter kategori berita">
                <span class="news-filter__label">Kategori</span>

                <a
                    href="{{ route('berita') }}"
                    @class(['is-active' => $activeCategorySlug === null])
                    @if ($activeCategorySlug === null) aria-current="page" @endif
                >
                    Semua
                </a>

                @foreach ($categories as $category)
                    <a
                        href="{{ route('berita', ['kategori' => $category['slug']]) }}"
                        @class(['is-active' => $activeCategorySlug === $category['slug']])
                        @if ($activeCategorySlug === $category['slug']) aria-current="page" @endif
                    >
                        {{ $category['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="news-grid">
                @foreach ($posts as $post)
                    <div data-reveal>
                        <x-news-card :post="$post" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
