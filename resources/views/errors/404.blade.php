<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#0d3525">
    <title>Halaman Tidak Ditemukan | {{ config('site.brand.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
</head>
<body class="error-page">
    <main class="error-shell">
        <a class="brand" href="{{ route('home') }}" aria-label="{{ config('site.brand.name') }} — Beranda">
            <img
                class="brand-logo"
                src="{{ asset(config('site.brand.logo')) }}"
                alt=""
                width="194"
                height="189"
            >
            <span class="brand-text">
                <strong>{{ config('site.brand.name') }}</strong>
                <small>{{ config('site.brand.tagline') }}</small>
            </span>
        </a>

        <div class="error-card">
            <span class="eyebrow eyebrow--gold">404</span>
            <h1>Halaman tidak ditemukan.</h1>
            <p>
                Alamat yang dibuka tidak tersedia atau sudah berpindah.
                Silakan kembali ke beranda untuk melanjutkan.
            </p>

            <div class="button-row">
                <a class="btn btn-gold" href="{{ route('home') }}">
                    Kembali ke Beranda
                </a>
                <a class="btn btn-ghost" href="{{ route('berita') }}">
                    Lihat Berita
                </a>
            </div>
        </div>

        <p class="error-help">
            Jika tautan ini berasal dari halaman lain, silakan gunakan navigasi utama situs.
        </p>
    </main>
</body>
</html>
