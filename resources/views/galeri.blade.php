@extends('layouts.public')

@section('title', "Galeri Foto | Roudlotul Qur'an")
@section('description', "Galeri foto dokumentasi Ribath Roudlotul Qur'an 1 — keseharian santri, tahfizh, kajian, dan kegiatan asrama.")

@php
    $firstItem = $gallery[0] ?? null;
    $firstImage = $firstItem && $firstItem['image'] ? config('site.images.' . $firstItem['image']) : null;
    $firstMediaId = $firstItem['media_id'] ?? null;
@endphp

@section('content')
    <x-page-hero
        title="Galeri Foto"
        eyebrow="Potret Suasana"
        description="Dokumentasi resmi keseharian santri: tahfizh, kajian, kegiatan, dan kebersamaan di asrama."
        image="galeri_hero"
    />

    <section class="section">
        <div class="container">
            <x-section-heading
                eyebrow="Galeri"
                title="Potret keseharian santri di asrama."
                description="Seluruh foto merupakan dokumentasi asli kegiatan Ribath Roudlotul Qur'an 1. Klik foto untuk melihat pratinjau."
            />

            @if (count($gallery))
                <div class="gallery-carousel" data-gallery-carousel aria-roledescription="carousel" aria-label="Galeri foto">
                    <div class="gallery-track" data-gallery-track tabindex="0" aria-label="Daftar foto — geser ke samping atau gunakan tombol panah">
                        @foreach ($gallery as $item)
                            @php
                                $image = $item['image'] ? config('site.images.' . $item['image']) : null;
                            @endphp

                            <figure class="gallery-item" data-reveal>
                                <button
                                    class="gallery-item__button"
                                    type="button"
                                    data-lightbox-trigger
                                    @if (! empty($item['media_id']))
                                        data-lightbox-src="{{ route('media.show', $item['media_id']) }}"
                                        data-lightbox-alt="{{ $item['caption'] }}"
                                    @elseif ($image)
                                        data-lightbox-src="{{ asset($image['path']) }}"
                                        data-lightbox-alt="{{ $image['alt'] }}"
                                    @endif
                                    data-lightbox-caption="{{ $item['caption'] }}"
                                    aria-label="Perbesar foto: {{ $item['caption'] }}"
                                >
                                    @if (! empty($item['media_id']))
                                        <img
                                            class="gallery-item__image site-image"
                                            src="{{ route('media.show', $item['media_id']) }}"
                                            alt="{{ $item['caption'] }}"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    @else
                                        <x-site-image :name="$item['image']" class="gallery-item__image" />
                                    @endif

                                    <span class="gallery-item__zoom" aria-hidden="true">
                                        <x-site-icon name="expand" :size="18" />
                                    </span>
                                </button>

                                <figcaption>
                                    <span>{{ $item['caption'] }}</span>
                                    <small>Dokumentasi asrama</small>
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>

                    <div class="gallery-carousel__bar">
                        <button class="gallery-carousel__button" type="button" data-gallery-prev aria-label="Slide sebelumnya">
                            <x-site-icon name="arrow-left" :size="18" />
                        </button>

                        <div class="gallery-carousel__dots" data-gallery-dots></div>

                        <button class="gallery-carousel__button" type="button" data-gallery-next aria-label="Slide berikutnya">
                            <x-site-icon name="arrow-right" :size="18" />
                        </button>
                    </div>
                </div>
            @else
                <p class="gallery-empty">Belum ada foto galeri yang dipublikasikan.</p>
            @endif
        </div>
    </section>

    @if ($firstItem && ($firstImage || $firstMediaId))
        <dialog class="lightbox" data-lightbox aria-labelledby="lightbox-caption">
            @if ($firstMediaId)
                <img
                    class="lightbox__image"
                    data-lightbox-image
                    src="{{ route('media.show', $firstMediaId) }}"
                    alt="{{ $firstItem['caption'] }}"
                >
            @else
                <img
                    class="lightbox__image"
                    data-lightbox-image
                    src="{{ asset($firstImage['path']) }}"
                    alt="{{ $firstImage['alt'] }}"
                    width="{{ $firstImage['width'] }}"
                    height="{{ $firstImage['height'] }}"
                >
            @endif

            <div class="lightbox__bar">
                <div class="lightbox__meta">
                    <p class="lightbox__caption" id="lightbox-caption" data-lightbox-caption>{{ $firstItem['caption'] }}</p>
                </div>

                <button class="lightbox__close" type="button" data-lightbox-close aria-label="Tutup pratinjau foto">
                    <x-site-icon name="close" :size="21" />
                </button>
            </div>
        </dialog>
    @endif
@endsection
