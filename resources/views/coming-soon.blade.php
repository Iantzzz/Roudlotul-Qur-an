@extends('layouts.public')

@section('title', $title . " | Roudlotul Qur'an")
@section('description', $description)

@section('content')
    <x-page-hero
        :title="$title"
        :description="$description"
        eyebrow="Segera hadir"
        image="mosque_light"
    />

    <section class="section">
        <div class="container container--narrow">
            <div class="coming-card" data-reveal>
                <span class="coming-card__icon" aria-hidden="true">
                    <x-site-icon name="clock" :size="34" />
                </span>

                <x-temp-badge text="Sedang disiapkan" />

                <h2>Halaman {{ $title }} segera hadir.</h2>

                <p>
                    Kami sedang menyiapkan informasi resmi untuk halaman ini.
                    Sementara itu, Anda tetap dapat menjelajahi beranda atau
                    melihat informasi kontak.
                </p>

                <div class="button-row">
                    <a class="btn btn-primary" href="{{ route('home') }}">
                        Kembali ke Beranda
                    </a>
                    <a class="btn btn-outline" href="{{ route('kontak') }}">
                        Halaman Kontak
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
