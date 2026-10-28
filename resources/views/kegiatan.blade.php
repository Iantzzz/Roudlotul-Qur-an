@extends('layouts.public')

@section('title', "Program & Kegiatan | Roudlotul Qur'an")
@section('description', "Gambaran program dan kegiatan Roudlotul Qur'an berdasarkan kelompok ibadah, pendidikan, pembinaan karakter, serta kemandirian. Konten masih berupa contoh.")

@section('content')
    <x-page-hero
        title="Program & Kegiatan"
        eyebrow="Kehidupan Asrama"
        description="Gambaran kegiatan dikelompokkan agar lebih mudah ditelusuri. Seluruh nama dan isi program masih berupa contoh."
        image="library"
    />

    <section class="section">
        <div class="container">
            <x-section-heading
                eyebrow="Program & Kegiatan"
                title="Ruang belajar yang tertata dalam beberapa kelompok."
                description="Susunan berikut hanya menggambarkan cara penataan halaman. Program, jadwal, dan kegiatan resmi akan ditambahkan setelah informasi dari pengurus tersedia."
                :temporary="true"
            />

            <div class="program-groups">
                @foreach ($programGroups as $group)
                    <section class="program-group" id="{{ $group['id'] }}" aria-label="{{ $group['title'] }}">
                        <x-section-heading
                            :eyebrow="$group['eyebrow']"
                            :title="$group['title']"
                            :description="$group['description']"
                        />

                        <div class="activity-grid">
                            @foreach ($group['activities'] as $activity)
                                <div data-reveal>
                                    <x-activity-card :activity="$activity" :show-link="false" />
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </section>
@endsection
