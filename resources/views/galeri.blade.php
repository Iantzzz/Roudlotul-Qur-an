@extends('layouts.public')

@section('title', "Galeri Foto | Roudlotul Qur'an")
@section('description', "Galeri foto dokumentasi Ribath Roudlotul Qur'an 1 — keseharian santri, tahfizh, kajian, dan kegiatan asrama.")

@php
    $firstImage = config('site.images.' . $gallery[0]['image']);
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

            <div class="gallery-carousel" data-gallery-carousel aria-roledescription="carousel" aria-label="Galeri foto">
                <div class="gallery-track" data-gallery-track tabindex="0" aria-label="Daftar foto — geser ke samping atau gunakan tombol panah">
                    @foreach ($gallery as $item)
                        @php
                            $image = config('site.images.' . $item['image']);
                        @endphp

                        <figure class="gallery-item" data-reveal>
                            <button
                                class="gallery-item__button"
                                type="button"
                                data-lightbox-trigger
                                data-lightbox-src="{{ asset($image['path']) }}"
                                data-lightbox-alt="{{ $image['alt'] }}"
                                data-lightbox-caption="{{ $item['caption'] }}"
                                aria-label="Perbesar foto: {{ $item['caption'] }}"
                            >
                                <x-site-image :name="$item['image']" class="gallery-item__image" />

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
        </div>
    </section>

    <dialog class="lightbox" data-lightbox aria-labelledby="lightbox-caption">
        <img
            class="lightbox__image"
            data-lightbox-image
            src="{{ asset($firstImage['path']) }}"
            alt="{{ $firstImage['alt'] }}"
            width="{{ $firstImage['width'] }}"
            height="{{ $firstImage['height'] }}"
        >

        <div class="lightbox__bar">
            <div class="lightbox__meta">
                <p class="lightbox__caption" id="lightbox-caption" data-lightbox-caption>{{ $gallery[0]['caption'] }}</p>
            </div>

            <button class="lightbox__close" type="button" data-lightbox-close aria-label="Tutup pratinjau foto">
                <x-site-icon name="close" :size="21" />
            </button>
        </div>
    </dialog>
@endsection
