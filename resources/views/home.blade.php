@extends('layouts.public')

@section('title', "Beranda | Roudlotul Qur'an")
@section('description', "Kenali asrama Roudlotul Qur'an: ruang belajar, menghafal Al-Qur'an, memperdalam ilmu, dan membangun akhlak dalam kebersamaan.")

@section('content')
    <section class="hero" aria-labelledby="hero-title">
        <x-site-image
            name="hero"
            class="hero__image"
            loading="eager"
            fetchpriority="high"
            width="1600"
            height="1000"
        />

        <div class="hero__overlay" aria-hidden="true"></div>

        <div class="container hero__inner">
            <div class="hero__content" data-reveal>
                <span class="eyebrow eyebrow--gold">Asrama Pendidikan Al-Qur’an</span>

                <h1 id="hero-title">
                    Menumbuhkan Generasi Qur’ani,
                    <span>Membangun Akhlak Mulia.</span>
                </h1>

                <p>
                    Ruang untuk belajar, menghafal Al-Qur’an, memperdalam ilmu,
                    dan membangun karakter dalam kebersamaan.
                </p>

                <div class="button-row">
                    <a class="btn btn-gold" href="{{ route('pendaftaran') }}">
                        Info Pendaftaran
                        <x-site-icon name="arrow-right" :size="18" />
                    </a>
                    <a class="btn btn-ghost" href="{{ route('profil') }}">
                        Kenali Kami
                    </a>
                </div>

                <ul class="hero__points" aria-label="Fokus pembinaan">
                    <li>
                        <x-site-icon name="check" :size="16" />
                        Belajar Al-Qur’an
                    </li>
                    <li>
                        <x-site-icon name="check" :size="16" />
                        Pembinaan akhlak
                    </li>
                    <li>
                        <x-site-icon name="check" :size="16" />
                        Kehidupan asrama
                    </li>
                </ul>
            </div>

            <aside class="hero__aside" data-reveal>
                <span class="hero__arabic" lang="ar" dir="rtl">بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</span>
                <p>Dengan ilmu, iman, dan akhlak.</p>
                <x-temp-badge text="Konten sementara" />
            </aside>
        </div>

        <a class="hero__scroll" href="#fondasi">
            <span>Jelajahi</span>
            <span class="hero__scroll-line" aria-hidden="true"></span>
        </a>
    </section>

    <section class="foundations" id="fondasi" aria-label="Fondasi pembinaan">
        <div class="container">
            <div class="foundations__grid">
                @foreach ($foundations as $item)
                    <article class="foundation-card" data-reveal>
                        <span class="foundation-card__icon" aria-hidden="true">
                            <x-site-icon :name="$item['icon']" :size="25" />
                        </span>
                        <div>
                            <h2>{{ $item['title'] }}</h2>
                            <p>{{ $item['text'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section overview" id="tentang">
        <div class="container split">
            <div class="split__media" data-reveal>
                <div class="media-frame">
                    <x-site-image name="quran_open" class="split__image" />
                </div>

                <div class="media-note">
                    <x-site-icon name="quote" :size="26" />
                    <p>Belajar dengan adab, tumbuh dalam kebersamaan.</p>
                    <span>Foto sementara</span>
                </div>
            </div>

            <div class="split__content" data-reveal>
                <x-section-heading
                    eyebrow="Tentang Asrama"
                    title="Ruang bertumbuh dalam ilmu, iman, dan adab."
                    description="Roudlotul Qur’an hadir sebagai ruang belajar yang menempatkan Al-Qur’an, adab, dan kebersamaan sebagai bagian penting dari keseharian."
                    :temporary="true"
                />

                <p>
                    Melalui pembelajaran dan pendampingan, santri diarahkan untuk
                    berkembang menjadi pribadi yang berilmu, mandiri, dan
                    bertanggung jawab. Profil lengkap serta sejarah resmi asrama
                    akan ditampilkan setelah informasi dari pengurus tersedia.
                </p>

                <ul class="check-list">
                    <li>
                        <x-site-icon name="check" :size="18" />
                        Pembelajaran dan pembiasaan yang terarah.
                    </li>
                    <li>
                        <x-site-icon name="check" :size="18" />
                        Lingkungan asrama yang mendukung kemandirian.
                    </li>
                    <li>
                        <x-site-icon name="check" :size="18" />
                        Pendampingan adab dan tanggung jawab.
                    </li>
                </ul>

                <a class="btn btn-primary" href="{{ route('profil') }}">
                    Kenali Profil Kami
                    <x-site-icon name="arrow-right" :size="17" />
                </a>
            </div>
        </div>
    </section>

    <section class="section section--deep pillars" id="pilar">
        <div class="container">
            <div class="section-toolbar">
                <x-section-heading
                    eyebrow="Pilar Pembinaan"
                    title="Empat fokus yang ingin kami rawat bersama."
                    description="Susunan pilar berikut masih berupa contoh dan akan disesuaikan dengan program resmi asrama."
                    theme="dark"
                    :temporary="true"
                />
            </div>

            <div class="pillar-grid">
                @foreach ($pillars as $pillar)
                    <article class="pillar-card" data-reveal>
                        <div class="pillar-card__top">
                            <span class="pillar-card__icon" aria-hidden="true">
                                <x-site-icon :name="$pillar['icon']" :size="26" />
                            </span>
                            <span class="pillar-card__number" aria-hidden="true">{{ $pillar['number'] }}</span>
                        </div>

                        <h3>{{ $pillar['title'] }}</h3>
                        <p>{{ $pillar['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section activities" id="kegiatan">
        <div class="container">
            <div class="section-toolbar">
                <x-section-heading
                    eyebrow="Kehidupan Asrama"
                    title="Belajar, beribadah, dan bertumbuh bersama."
                    description="Gambaran kegiatan di bawah ini masih berupa contoh. Program resmi akan diumumkan kemudian."
                    :temporary="true"
                />

                <a class="btn btn-outline section-toolbar__action" href="{{ route('kegiatan') }}">
                    Semua Kegiatan
                    <x-site-icon name="arrow-up-right" :size="17" />
                </a>
            </div>

            <div class="activity-grid">
                @foreach ($activities as $activity)
                    <div data-reveal>
                        <x-activity-card :activity="$activity" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section section--cream news" id="berita">
        <div class="container">
            <div class="section-toolbar">
                <x-section-heading
                    eyebrow="Berita Terbaru"
                    title="Kabar dari Roudlotul Qur’an."
                    description="Tiga kartu berikut adalah contoh tampilan berita. Isi dan tanggal resmi akan segera menggantikannya."
                    :temporary="true"
                />

                <a class="btn btn-outline section-toolbar__action" href="{{ route('berita') }}">
                    Semua Berita
                    <x-site-icon name="arrow-up-right" :size="17" />
                </a>
            </div>

            <div class="news-grid">
                @foreach ($news as $post)
                    <div data-reveal>
                        <x-news-card :post="$post" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section gallery" id="galeri">
        <div class="container">
            <div class="section-toolbar">
                <x-section-heading
                    eyebrow="Galeri"
                    title="Potret suasana yang ingin kami hadirkan."
                    description="Seluruh foto pada galeri ini adalah aset sementara dari Unsplash, bukan dokumentasi resmi asrama."
                    :temporary="true"
                />

                <a class="btn btn-outline section-toolbar__action" href="{{ route('galeri') }}">
                    Buka Galeri
                    <x-site-icon name="arrow-up-right" :size="17" />
                </a>
            </div>

            <div class="gallery-grid">
                @foreach ($gallery as $item)
                    <figure class="gallery-item" data-reveal>
                        <x-site-image :name="$item['image']" class="gallery-item__image" />

                        <figcaption>
                            <span>{{ $item['caption'] }}</span>
                            <small>Foto sementara</small>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section cta-section">
        <div class="container">
            <div class="cta-panel" data-reveal>
                <div class="cta-panel__content">
                    <span class="eyebrow eyebrow--gold">Pendaftaran</span>
                    <h2>Siap mengenal Roudlotul Qur’an lebih dekat?</h2>
                    <p>
                        Informasi alur, syarat, jadwal, dan biaya pendaftaran resmi
                        akan diumumkan melalui halaman pendaftaran.
                    </p>

                    <div class="button-row">
                        <a class="btn btn-gold" href="{{ route('pendaftaran') }}">
                            Lihat Info Pendaftaran
                            <x-site-icon name="arrow-right" :size="18" />
                        </a>
                        <a class="btn btn-ghost" href="{{ route('kontak') }}">
                            Hubungi Kami
                        </a>
                    </div>
                </div>

                <div class="cta-panel__aside">
                    <span class="cta-panel__icon" aria-hidden="true">
                        <x-site-icon name="sparkle" :size="30" />
                    </span>
                    <p>Jadwal dan biaya resmi belum tersedia.</p>
                    <x-temp-badge text="Menunggu info resmi" />
                </div>
            </div>
        </div>
    </section>
@endsection
