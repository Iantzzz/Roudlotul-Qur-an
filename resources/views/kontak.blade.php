@extends('layouts.public')

@section('title', "Kontak | Roudlotul Qur'an")
@section('description', "Informasi kontak, kanal komunikasi, peta, dan formulir tampilan Roudlotul Qur'an. Data resmi masih berupa placeholder.")

@section('content')
    <x-page-hero
        title="Kontak"
        eyebrow="Hubungi Kami"
        description="Informasi alamat, telepon, email, dan kanal komunikasi resmi masih dalam proses pelengkapan."
        image="quran_open"
    />

    <section class="section">
        <div class="container">
            <x-section-heading
                eyebrow="Informasi Kontak"
                title="Kanal komunikasi yang akan tersedia."
                description="Semua detail kontak pada halaman ini masih berupa placeholder. Formulir dan peta belum aktif."
                :temporary="true"
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
                            </div>
                        </li>
                        <li>
                            <span class="contact-details__icon" aria-hidden="true">
                                <x-site-icon name="phone" :size="21" />
                            </span>
                            <div>
                                <span>Telepon</span>
                                <p>{{ config('site.contact.phone') }}</p>
                            </div>
                        </li>
                        <li>
                            <span class="contact-details__icon" aria-hidden="true">
                                <x-site-icon name="phone" :size="21" />
                            </span>
                            <div>
                                <span>WhatsApp</span>
                                <p>{{ config('site.contact.whatsapp') }}</p>
                            </div>
                        </li>
                        <li>
                            <span class="contact-details__icon" aria-hidden="true">
                                <x-site-icon name="mail" :size="21" />
                            </span>
                            <div>
                                <span>Email</span>
                                <p>{{ config('site.contact.email') }}</p>
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
                                <span class="social-link social-link--disabled">
                                    <x-site-icon :name="$social['icon']" :size="18" />
                                    {{ $social['label'] }}
                                    <small>Segera</small>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <x-temp-badge text="Kontak resmi belum tersedia" />
                </div>

                <div class="profile-panel contact-form-panel" data-reveal>
                    <span class="eyebrow">Formulir</span>
                    <h2>Kirim pesan.</h2>
                    <p>
                        Formulir berikut hanya menampilkan rancangan tampilan.
                        Tidak ada data yang dikirim atau disimpan.
                    </p>

                    <form class="contact-form" aria-describedby="contact-form-note" novalidate>
                        <fieldset disabled>
                            <legend class="sr-only">Formulir kontak tampilan</legend>

                            <div class="form-grid">
                                <label class="form-field">
                                    <span>Nama</span>
                                    <input type="text" name="name" placeholder="[Nama lengkap]">
                                </label>

                                <label class="form-field">
                                    <span>Email</span>
                                    <input type="email" name="email" placeholder="[Alamat email]">
                                </label>

                                <label class="form-field form-field--full">
                                    <span>Subjek</span>
                                    <input type="text" name="subject" placeholder="[Subjek pesan]">
                                </label>

                                <label class="form-field form-field--full">
                                    <span>Pesan</span>
                                    <textarea name="message" rows="5" placeholder="[Isi pesan]"></textarea>
                                </label>
                            </div>

                            <button class="btn btn-primary" type="button" disabled>
                                Kirim Pesan
                                <x-site-icon name="arrow-right" :size="17" />
                            </button>
                        </fieldset>
                    </form>

                    <div class="form-note" id="contact-form-note">
                        <x-temp-badge text="Formulir aktif di Tahap 2" />
                        <p>Formulir ini tidak terhubung ke email, database, atau layanan pengiriman pesan.</p>
                    </div>
                </div>
            </div>

            <div class="map-placeholder" role="group" aria-label="Placeholder peta lokasi asrama" data-reveal>
                <span class="map-placeholder__icon" aria-hidden="true">
                    <x-site-icon name="map-pin" :size="32" />
                </span>
                <div>
                    <span class="eyebrow eyebrow--gold">Peta Lokasi</span>
                    <h2>Peta resmi akan ditanamkan di sini.</h2>
                    <p>
                        Lokasi dan koordinat belum tersedia. Peta tidak ditampilkan
                        sebelum data lokasi resmi dari pengurus asrama diterima.
                    </p>
                </div>
                <x-temp-badge text="Lokasi belum tersedia" />
            </div>
        </div>
    </section>
@endsection
