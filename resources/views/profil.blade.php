@extends('layouts.public')

@section('title', "Profil Asrama | Roudlotul Qur'an")
@section('description', "Profil Ribath Roudlotul Qur'an 1, asrama pendidikan Al-Qur'an di lingkungan Pondok Pesantren Darul Ulum, Rejoso, Peterongan, Jombang.")

@section('content')
    <x-page-hero
        title="Profil Asrama"
        eyebrow="Mengenal Kami"
        description="Ribath Roudlotul Qur'an 1 — asrama pendidikan Al-Qur'an di lingkungan Pondok Pesantren Darul Ulum, Rejoso, Peterongan, Jombang."
        image="calligraphy_gold"
    />

    <nav class="page-anchor-nav" aria-label="Bagian halaman profil">
        <div class="container page-anchor-nav__inner">
            <span class="page-anchor-nav__label">Bagian</span>
            <div class="page-anchor-nav__links">
                @if ($greeting)
                    <a href="#sambutan">Sambutan</a>
                @endif
                <a href="#profil-singkat">Profil Singkat</a>
                <a href="#sejarah">Sejarah</a>
                <a href="#visi-misi">Visi &amp; Misi</a>
                <a href="#kepengurusan">Kepengurusan</a>
                <a href="#nilai">Nilai-nilai</a>
            </div>
        </div>
    </nav>

    @php
        $greetingParagraphs = $greeting
            ? collect(preg_split('/\r\n|\r/', (string) $greeting->body))
                ->map(fn (string $paragraph): string => trim($paragraph))
                ->filter()
                ->values()
            : collect();
    @endphp

    @if ($greeting)
        <section class="section section--cream profile-section" id="sambutan">
            <div class="container split split--profile">
                <div class="split__media" data-reveal>
                    <div class="media-frame greeting__frame">
                        @if ($greeting->site_media_id)
                            <img
                                class="greeting__photo"
                                src="{{ route('media.show', $greeting->site_media_id) }}"
                                alt="Foto {{ $greeting->name }}"
                                loading="lazy"
                                decoding="async"
                            >
                        @else
                            <div class="greeting__photo greeting__photo--placeholder" aria-hidden="true">
                                <x-site-icon name="quote" :size="36" />
                            </div>
                        @endif
                    </div>
                </div>

                <div class="split__content" data-reveal>
                    <x-section-heading
                        eyebrow="Sambutan"
                        title="Pesan pembina untuk santri."
                    />

                    <blockquote class="greeting__quote">
                        @foreach ($greetingParagraphs as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </blockquote>

                    <footer class="greeting__identity">
                        <strong>{{ $greeting->name }}</strong>
                        <span>{{ $greeting->role }}</span>
                    </footer>
                </div>
            </div>
        </section>
    @endif

    <section class="section profile-section" id="profil-singkat">
        <div class="container split split--profile">
            <div class="split__media" data-reveal>
                <div
                    class="media-frame split-slider"
                    data-split-slider
                    data-split-interval="4000"
                    role="region"
                    aria-roledescription="carousel"
                    aria-label="Foto dokumentasi asrama"
                >
                    <div class="split-slider__track" data-split-track tabindex="0" aria-label="Foto dokumentasi — geser ke samping atau gunakan panah keyboard">
                        @foreach (['quran_manuscript', 'mosque_light', 'calligraphy_gold'] as $splitImage)
                            <x-site-image :name="$splitImage" class="split-slider__slide" />
                        @endforeach
                    </div>

                    <div class="split-slider__dots" data-split-dots></div>
                </div>

                <div class="media-note">
                    <x-site-icon name="quote" :size="26" />
                    <p>{{ config('site.brand.motto.translation') }}</p>
                    <span>Foto sementara</span>
                </div>
            </div>

            <div class="split__content" data-reveal>
                <x-section-heading
                    eyebrow="Profil Singkat"
                    title="Ribath Roudlotul Qur'an 1."
                    description="Asrama pendidikan Al-Qur'an di lingkungan Pondok Pesantren Darul Ulum, Rejoso, Peterongan, Jombang."
                />

                <p>
                    Ribath Roudlotul Qur'an 1 adalah asrama pendidikan Al-Qur'an
                    di lingkungan Pondok Pesantren Darul Ulum, Rejoso,
                    Peterongan, Jombang. Melalui pembelajaran dan pendampingan,
                    santri dibina untuk tumbuh berilmu, mandiri, dan berakhlak.
                </p>

                <dl class="profile-facts">
                    <div>
                        <dt>Nama</dt>
                        <dd>{{ config('site.brand.official_name') }}</dd>
                    </div>
                    <div>
                        <dt>Jenis</dt>
                        <dd>{{ config('site.brand.tagline') }}</dd>
                    </div>
                    <div>
                        <dt>Induk</dt>
                        <dd>Pondok Pesantren Darul Ulum</dd>
                    </div>
                    <div>
                        <dt>Lokasi</dt>
                        <dd>Rejoso, Peterongan, Jombang</dd>
                    </div>
                    <div>
                        <dt>Pengasuh</dt>
                        <dd>Drs. KH. Cholil Dahlan<br>Ibu Nyai Hj. Anisatus Sa'diyah</dd>
                    </div>
                    <div>
                        <dt>Tahun berdiri</dt>
                        <dd>[Tahun resmi]</dd>
                    </div>
                </dl>

                <div class="button-row">
                    <a class="btn btn-primary" href="#sejarah">
                        Telusuri Profil
                        <x-site-icon name="arrow-right" :size="17" />
                    </a>
                    <a class="btn btn-outline" href="{{ route('kegiatan') }}">
                        Lihat Kegiatan
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="section section--cream profile-section" id="sejarah">
        <div class="container">
            <div class="section-toolbar">
                <x-section-heading
                    eyebrow="Sejarah"
                    title="Perjalanan yang akan dicatat secara resmi."
                    description="Latar belakang, tahun berdiri, dan tonggak penting asrama akan diisi setelah data dari pengurus tersedia."
                    :temporary="true"
                />
            </div>

            <div class="profile-panels">
                <article class="profile-panel" data-reveal>
                    <span class="profile-panel__icon" aria-hidden="true">
                        <x-site-icon name="calendar" :size="24" />
                    </span>

                    <h3>Sejarah Singkat</h3>
                    <p class="profile-panel__quote">[Isi sejarah resmi asrama]</p>
                    <p>
                        Bagian ini disiapkan untuk memuat latar belakang pendirian,
                        tahun berdiri, dan perjalanan asrama sesuai catatan resmi.
                    </p>
                    <x-temp-badge text="Menunggu sejarah resmi" />
                </article>

                <article class="profile-panel" data-reveal>
                    <span class="profile-panel__icon" aria-hidden="true">
                        <x-site-icon name="clock" :size="24" />
                    </span>

                    <h3>Tonggak Penting</h3>
                    <p class="profile-panel__quote">[Tahun dan peristiwa penting asrama]</p>
                    <p>
                        Tonggak sejarah tidak akan ditampilkan sebelum tahun dan
                        peristiwanya dipastikan oleh pengurus asrama.
                    </p>
                    <x-temp-badge text="Data belum tersedia" />
                </article>
            </div>
        </div>
    </section>

    <section class="section profile-section" id="visi-misi">
        <div class="container">
            <div class="section-toolbar">
                <x-section-heading
                    eyebrow="Arah Asrama"
                    title="Visi dan misi yang menjadi arah bersama."
                    description="Rumusan resmi visi dan misi belum tersedia. Teks pada bagian ini tidak mewakili pernyataan resmi asrama."
                    :temporary="true"
                />
            </div>

            <div class="profile-panels">
                <article class="profile-panel" data-reveal>
                    <span class="profile-panel__icon" aria-hidden="true">
                        <x-site-icon name="sparkle" :size="24" />
                    </span>

                    <h3>Visi</h3>
                    <p class="profile-panel__quote">[Isi visi resmi asrama]</p>
                    <p>
                        Visi resmi akan dicantumkan setelah rumusan dari pengurus
                        asrama tersedia.
                    </p>
                    <x-temp-badge text="Visi belum tersedia" />
                </article>

                <article class="profile-panel" data-reveal>
                    <span class="profile-panel__icon" aria-hidden="true">
                        <x-site-icon name="check" :size="24" />
                    </span>

                    <h3>Misi</h3>
                    <ul class="check-list">
                        <li>
                            <x-site-icon name="check" :size="17" />
                            [Butir misi resmi 1]
                        </li>
                        <li>
                            <x-site-icon name="check" :size="17" />
                            [Butir misi resmi 2]
                        </li>
                        <li>
                            <x-site-icon name="check" :size="17" />
                            [Butir misi resmi 3]
                        </li>
                        <li>
                            <x-site-icon name="check" :size="17" />
                            [Butir misi resmi 4]
                        </li>
                    </ul>
                    <x-temp-badge text="Misi belum tersedia" />
                </article>
            </div>
        </div>
    </section>

    <section class="section profile-section" id="kepengurusan">
        <div class="container">
            <div class="section-toolbar">
                <x-section-heading
                    eyebrow="Struktur Kepengurusan"
                    title="Susunan pengurus yang bertugas saat ini."
                    description="Struktur kepengurusan diperbarui tiap pergantian periode dan hanya diisi melalui panel admin; periode sebelumnya tetap tersimpan sebagai riwayat."
                />
            </div>

            @if ($kepengurusan)
                <p class="org-period">Periode {{ $kepengurusan->label }}</p>

                <div class="org-chart">
                    @forelse ($struktur['roots'] ?? [] as $node)
                        <x-management-node :node="$node" />
                    @empty
                        <p class="org-empty">Bagan belum diatur; admin menentukan struktur dari panel Filament.</p>
                    @endforelse
                </div>
            @else
                <div class="profile-panel" data-reveal>
                    <span class="profile-panel__icon" aria-hidden="true">
                        <x-site-icon name="users" :size="24" />
                    </span>

                    <h3>Struktur Kepengurusan</h3>
                    <p>
                        Susunan pengurus akan diterbitkan setelah periode yang berjalan
                        dipastikan oleh admin. Periode sebelumnya tetap tersimpan.
                    </p>
                    <x-temp-badge text="Struktur belum dipublikasikan" />
                </div>
            @endif
        </div>
    </section>

    <section class="section section--deep profile-section" id="nilai">
        <div class="container">
            <div class="section-toolbar">
                <x-section-heading
                    eyebrow="Nilai-nilai"
                    title="Arah nilai yang ingin dirawat."
                    description="Nilai-nilai disarikan dari pesan pembinaan dan kegiatan yang dibagikan secara resmi oleh pengurus."
                    theme="dark"
                />
            </div>

            <div class="pillar-grid">
                @foreach ($values as $value)
                    <article class="pillar-card" data-reveal>
                        <div class="pillar-card__top">
                            <span class="pillar-card__icon" aria-hidden="true">
                                <x-site-icon :name="$value['icon']" :size="26" />
                            </span>
                            <span class="pillar-card__number" aria-hidden="true">{{ $value['number'] }}</span>
                        </div>

                        <h3>{{ $value['title'] }}</h3>
                        <p>{{ $value['text'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
