@extends('layouts.public')

@section('title', "Pendaftaran | Roudlotul Qur'an")
@section('description', "Gambaran alur, syarat, jadwal, biaya, dan FAQ pendaftaran Roudlotul Qur'an. Tanggal dan biaya resmi masih berupa placeholder.")

@section('content')
    <x-page-hero
        title="Pendaftaran"
        eyebrow="Bergabung Bersama"
        description="Halaman ini menyiapkan gambaran alur pendaftaran. Tanggal, biaya, dan ketentuan resmi belum tersedia."
        image="courtyard"
    />

    <section class="section">
        <div class="container">
            <div class="section-toolbar">
                <x-section-heading
                    eyebrow="Alur Pendaftaran"
                    title="Langkah yang akan dilalui calon peserta."
                    description="Urutan berikut adalah contoh penataan. Prosedur resmi akan diumumkan oleh pengurus asrama."
                    :temporary="true"
                />
            </div>

            <ol class="process-steps">
                <li class="process-step" data-reveal>
                    <span class="process-step__icon" aria-hidden="true">
                        <x-site-icon name="phone" :size="24" />
                    </span>
                    <span class="process-step__label">Langkah Satu</span>
                    <h3>Kontak Awal</h3>
                    <p>Calon peserta menghubungi narahubung resmi untuk menanyakan informasi pendaftaran.</p>
                </li>

                <li class="process-step" data-reveal>
                    <span class="process-step__icon" aria-hidden="true">
                        <x-site-icon name="book" :size="24" />
                    </span>
                    <span class="process-step__label">Langkah Dua</span>
                    <h3>Pengisian Formulir</h3>
                    <p>Formulir dan data calon peserta diisi sesuai petunjuk resmi yang akan disediakan.</p>
                </li>

                <li class="process-step" data-reveal>
                    <span class="process-step__icon" aria-hidden="true">
                        <x-site-icon name="check" :size="24" />
                    </span>
                    <span class="process-step__label">Langkah Tiga</span>
                    <h3>Verifikasi Berkas</h3>
                    <p>Berkas dan persyaratan akan diperiksa oleh pengurus sesuai ketentuan pendaftaran.</p>
                </li>

                <li class="process-step" data-reveal>
                    <span class="process-step__icon" aria-hidden="true">
                        <x-site-icon name="sparkle" :size="24" />
                    </span>
                    <span class="process-step__label">Langkah Empat</span>
                    <h3>Pengumuman</h3>
                    <p>Hasil dan informasi lanjutan akan disampaikan melalui kanal resmi asrama.</p>
                </li>
            </ol>
        </div>
    </section>

    <section class="section section--cream">
        <div class="container">
            <div class="section-toolbar">
                <x-section-heading
                    eyebrow="Persyaratan"
                    title="Dokumen dan ketentuan yang perlu disiapkan."
                    description="Daftar persyaratan resmi belum tersedia. Semua butir pada bagian ini masih berupa placeholder."
                    :temporary="true"
                />
            </div>

            <div class="profile-panels">
                <article class="profile-panel" data-reveal>
                    <span class="profile-panel__icon" aria-hidden="true">
                        <x-site-icon name="book" :size="24" />
                    </span>

                    <h3>Dokumen Pendaftaran</h3>
                    <ul class="check-list">
                        <li>
                            <x-site-icon name="check" :size="17" />
                            [Dokumen persyaratan resmi 1]
                        </li>
                        <li>
                            <x-site-icon name="check" :size="17" />
                            [Dokumen persyaratan resmi 2]
                        </li>
                        <li>
                            <x-site-icon name="check" :size="17" />
                            [Dokumen persyaratan resmi 3]
                        </li>
                        <li>
                            <x-site-icon name="check" :size="17" />
                            [Dokumen tambahan resmi]
                        </li>
                    </ul>
                    <x-temp-badge text="Daftar syarat belum tersedia" />
                </article>

                <article class="profile-panel" data-reveal>
                    <span class="profile-panel__icon" aria-hidden="true">
                        <x-site-icon name="users" :size="24" />
                    </span>

                    <h3>Ketentuan Peserta</h3>
                    <p>
                        Ketentuan usia, jenjang, domisili, dan persyaratan khusus
                        belum tersedia untuk dipublikasikan.
                    </p>
                    <p>
                        Pengurus asrama akan menetapkan seluruh ketentuan resmi
                        sebelum pendaftaran dibuka.
                    </p>
                    <x-temp-badge text="Ketentuan resmi menyusul" />
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-toolbar">
                <x-section-heading
                    eyebrow="Jadwal"
                    title="Waktu pendaftaran yang akan diumumkan."
                    description="Tidak ada tanggal yang ditampilkan sebagai jadwal berlaku. Seluruh kolom di bawah menunggu data resmi."
                    :temporary="true"
                />
            </div>

            <div class="profile-panels">
                <article class="profile-panel" data-reveal>
                    <span class="profile-panel__icon" aria-hidden="true">
                        <x-site-icon name="calendar" :size="24" />
                    </span>

                    <h3>Jadwal Pendaftaran</h3>
                    <dl class="profile-facts">
                        <div>
                            <dt>Gelombang pertama</dt>
                            <dd>[Tanggal resmi]</dd>
                        </div>
                        <div>
                            <dt>Gelombang berikutnya</dt>
                            <dd>[Tanggal resmi]</dd>
                        </div>
                        <div>
                            <dt>Batas pendaftaran</dt>
                            <dd>[Tanggal resmi]</dd>
                        </div>
                        <div>
                            <dt>Pengumuman hasil</dt>
                            <dd>[Tanggal resmi]</dd>
                        </div>
                    </dl>
                    <x-temp-badge text="Jadwal belum tersedia" />
                </article>

                <article class="profile-panel" data-reveal>
                    <span class="profile-panel__icon" aria-hidden="true">
                        <x-site-icon name="clock" :size="24" />
                    </span>

                    <h3>Waktu Layanan</h3>
                    <dl class="profile-facts">
                        <div>
                            <dt>Hari layanan</dt>
                            <dd>[Hari resmi]</dd>
                        </div>
                        <div>
                            <dt>Jam layanan</dt>
                            <dd>[Waktu resmi]</dd>
                        </div>
                    </dl>
                    <p>
                        Jam layanan administrasi akan mengikuti ketentuan resmi
                        dari pengurus asrama.
                    </p>
                    <x-temp-badge text="Waktu belum tersedia" />
                </article>
            </div>
        </div>
    </section>

    <section class="section section--cream">
        <div class="container">
            <div class="section-toolbar">
                <x-section-heading
                    eyebrow="Biaya"
                    title="Rincian biaya yang menunggu ketetapan resmi."
                    description="Halaman ini tidak mencantumkan nominal. Biaya pendaftaran dan biaya asrama akan diisi setelah keputusan resmi."
                    :temporary="true"
                />
            </div>

            <div class="profile-panels">
                <article class="profile-panel" data-reveal>
                    <span class="profile-panel__icon" aria-hidden="true">
                        <x-site-icon name="sparkle" :size="24" />
                    </span>

                    <h3>Biaya Pendaftaran</h3>
                    <dl class="profile-facts">
                        <div>
                            <dt>Biaya formulir</dt>
                            <dd>[Nominal resmi]</dd>
                        </div>
                        <div>
                            <dt>Biaya pendaftaran</dt>
                            <dd>[Nominal resmi]</dd>
                        </div>
                    </dl>
                    <x-temp-badge text="Nominal belum tersedia" />
                </article>

                <article class="profile-panel" data-reveal>
                    <span class="profile-panel__icon" aria-hidden="true">
                        <x-site-icon name="home" :size="24" />
                    </span>

                    <h3>Biaya Asrama</h3>
                    <dl class="profile-facts">
                        <div>
                            <dt>Biaya asrama</dt>
                            <dd>[Nominal resmi]</dd>
                        </div>
                        <div>
                            <dt>Metode pembayaran</dt>
                            <dd>[Metode resmi]</dd>
                        </div>
                        <div>
                            <dt>Kebijakan pengembalian</dt>
                            <dd>[Ketentuan resmi]</dd>
                        </div>
                    </dl>
                    <x-temp-badge text="Rincian belum tersedia" />
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container container--narrow">
            <div class="section-toolbar">
                <x-section-heading
                    eyebrow="FAQ"
                    title="Pertanyaan yang sering diajukan."
                    description="Jawaban berikut menjelaskan status informasi sementara, bukan ketentuan pendaftaran resmi."
                    :temporary="true"
                />
            </div>

            <div class="faq-list">
                <details class="faq-item" data-reveal>
                    <summary>Apakah jadwal pendaftaran sudah tersedia?</summary>
                    <p>Belum. Tanggal pendaftaran resmi belum diumumkan melalui situs ini.</p>
                </details>

                <details class="faq-item" data-reveal>
                    <summary>Berapa biaya pendaftaran dan biaya asrama?</summary>
                    <p>Nominal biaya belum tersedia. Halaman ini tidak menggunakan angka sementara sebagai pengganti biaya resmi.</p>
                </details>

                <details class="faq-item" data-reveal>
                    <summary>Dokumen apa yang perlu disiapkan?</summary>
                    <p>Daftar dokumen resmi belum tersedia. Persyaratan akan diumumkan bersama pembukaan pendaftaran.</p>
                </details>

                <details class="faq-item" data-reveal>
                    <summary>Bagaimana cara menanyakan informasi lain?</summary>
                    <p>
                        Silakan gunakan halaman
                        <a class="text-link" href="{{ route('kontak') }}">Kontak</a>
                        untuk melihat kanal komunikasi yang sedang disiapkan.
                    </p>
                </details>
            </div>
        </div>
    </section>

    <section class="section cta-section">
        <div class="container">
            <div class="cta-panel" data-reveal>
                <div class="cta-panel__content">
                    <span class="eyebrow eyebrow--gold">Informasi Resmi</span>
                    <h2>Menunggu informasi pendaftaran resmi?</h2>
                    <p>
                        Halaman kontak menyiapkan kanal komunikasi sementara.
                        Informasi pendaftaran akan diperbarui setelah data resmi tersedia.
                    </p>

                    <div class="button-row">
                        <a class="btn btn-gold" href="{{ route('kontak') }}">
                            Buka Halaman Kontak
                            <x-site-icon name="arrow-right" :size="18" />
                        </a>
                        <a class="btn btn-ghost" href="{{ route('berita') }}">
                            Lihat Berita
                        </a>
                    </div>
                </div>

                <div class="cta-panel__aside">
                    <span class="cta-panel__icon" aria-hidden="true">
                        <x-site-icon name="calendar" :size="30" />
                    </span>
                    <p>Tanggal, biaya, dan ketentuan resmi belum tersedia.</p>
                    <x-temp-badge text="Konten sementara" />
                </div>
            </div>
        </div>
    </section>
@endsection
