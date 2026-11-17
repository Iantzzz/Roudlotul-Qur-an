@extends('layouts.public')

@section('title', "Program & Kegiatan | Roudlotul Qur'an")
@section('description', "Program dan kegiatan Ribath Roudlotul Qur'an 1: tahfizh, kajian, pembinaan karakter, dan tradisi kebersamaan santri.")

@section('content')
    <x-page-hero
        title="Program & Kegiatan"
        eyebrow="Kehidupan Asrama"
        description="Kegiatan rutin Ribath Roudlotul Qur'an 1 — dari tahfizh dan kajian hingga pembinaan karakter dan tradisi kebersamaan."
        image="kegiatan_hero"
    />

    <section class="section">
        <div class="container">
            <x-section-heading
                eyebrow="Program & Kegiatan"
                title="Ruang belajar yang tertata dalam beberapa kelompok."
                description="Kegiatan berikut dirangkum dari dokumentasi resmi ribath di YouTube, Instagram, dan TikTok."
            />

            <div class="program-groups">
                @forelse ($programGroups as $group)
                    <section class="program-group" id="{{ $group['id'] }}" aria-label="{{ $group['title'] }}">
                        <x-section-heading
                            :eyebrow="$group['eyebrow']"
                            :title="$group['title']"
                            :description="$group['description']"
                        />

                        <div class="activity-grid">
                            @forelse ($group['activities'] as $activity)
                                <div data-reveal>
                                    <x-activity-card :activity="$activity" :show-link="false" />
                                </div>
                            @empty
                                <p class="section-empty section-empty--compact">
                                    Kegiatan untuk kelompok ini belum diisi melalui panel admin.
                                </p>
                            @endforelse
                        </div>
                    </section>
                @empty
                    <p class="section-empty">
                        Belum ada kelompok program yang dipublikasikan.
                        Daftar kegiatan diisi melalui panel admin.
                    </p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
