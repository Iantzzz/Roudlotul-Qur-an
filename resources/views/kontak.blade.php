@extends('layouts.public')

@section('title', "Kontak | Roudlotul Qur'an")
@section('description', "Narahubung pendaftaran, alamat asrama, dan kanal media sosial resmi Ribath Roudlotul Qur'an 1 di lingkungan Pondok Pesantren Darul Ulum, Rejoso, Jombang.")

@section('content')
    <x-page-hero
        title="Kontak"
        eyebrow="Hubungi Kami"
        description="Narahubung pendaftaran, alamat asrama, dan kanal media sosial resmi Ribath Roudlotul Qur'an 1."
        image="quran_open"
    />

    <section class="section">
        <div class="container">
            <x-section-heading
                eyebrow="Informasi Kontak"
                title="Hubungi pengurus atau narahubung pendaftaran."
                description="Silakan hubungi narahubung berikut untuk informasi pendaftaran, kegiatan, dan kehidupan asrama."
            />

            <div class="contact-layout">
                <div class="profile-panel contact-panel" data-reveal>
                    <span class="eyebrow">Detail Kontak</span>
                    <h2>Hubungi pengurus asrama.</h2>

                    <ul class="contact-details">
                        <li>
                            <span class="contact-details__icon" aria-hidden="true">
                                <x-site-icon name="map-pin" :size="21" />
                            </span>
                            <div>
                                <span>Alamat</span>
                                <p>{{ config('site.contact.address') }}</p>
                                <a
                                    class="text-link"
                                    href="https://www.google.com/maps/search/?api=1&query=Pondok+Pesantren+Darul+Ulum+Rejoso+Peterongan+Jombang"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    Buka di Google Maps
                                    <x-site-icon name="arrow-up-right" :size="14" />
                                </a>
                            </div>
                        </li>
                        <li>
                            <span class="contact-details__icon" aria-hidden="true">
                                <x-site-icon name="clock" :size="21" />
                            </span>
                            <div>
                                <span>Jam Layanan</span>
                                <p>{{ config('site.contact.hours') }}</p>
                            </div>
                        </li>
                    </ul>

                    <div class="contact-socials">
                        <span class="contact-socials__label">Media Sosial</span>
                        <div class="contact-socials__list">
                            @foreach (config('site.socials') as $social)
                                @if ($social['url'])
                                    <a class="social-link" href="{{ $social['url'] }}" rel="noopener noreferrer" target="_blank">
                                        <x-site-icon :name="$social['icon']" :size="18" />
                                        {{ $social['label'] }}
                                    </a>
                                @else
                                    <span class="social-link social-link--disabled">
                                        <x-site-icon :name="$social['icon']" :size="18" />
                                        {{ $social['label'] }}
                                        <small>Segera</small>
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <p class="contact-panel__note">
                        Untuk kunjungan langsung, mohon menghubungi narahubung
                        pendaftaran terlebih dahulu.
                    </p>
                </div>

                <div class="profile-panel contact-form-panel">
                    <span class="eyebrow">Narahubung Pendaftaran</span>
                    <h2>Narahubung pendaftaran.</h2>
                    <p>
                        Narahubung berikut siap membantu informasi pendaftaran
                        santri baru dan kegiatan asrama.
                    </p>

                    <x-registration-contacts />
                </div>
            </div>
        </div>
    </section>
@endsection
