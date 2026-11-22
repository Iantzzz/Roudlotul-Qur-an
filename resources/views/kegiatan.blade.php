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

            <div class="program-frequencies">

                {{-- Setiap Hari --}}
                <section class="program-frequency" id="ibadah">
                    <div class="frequency-header">
                        <div class="frequency-icon">
                            <x-site-icon name="calendar" :size="24" />
                        </div>
                        <div>
                            <h2 class="frequency-title">Setiap hari</h2>
                            <p class="frequency-desc">Ibadah dan rutinitas harian</p>
                        </div>
                    </div>
                    <ul class="frequency-list">
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Sholat Jama'ah di Masjid</span>
                                <span class="activity-sub">Shubuh, Maghrib & Isya'</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Ubudiyah Pagi</span>
                                <span class="activity-sub">Terbit matahari</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Apel Pagi & Do'a</span>
                                <span class="activity-sub">Sebelum berangkat sekolah & pengambilan uang</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Ngaji Qur'an</span>
                                <span class="activity-sub">Ba'd Maghrib & Setoran hafalan</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Ngaji Diniyah</span>
                                <span class="activity-sub">Setiap hari kecuali Senin & Kamis (libur)</span>
                            </div>
                        </li>
                    </ul>
                </section>

                {{-- Setiap Minggu --}}
                <section class="program-frequency" id="pendidikan">
                    <div class="frequency-header">
                        <div class="frequency-icon">
                            <x-site-icon name="book" :size="24" />
                        </div>
                        <div>
                            <h2 class="frequency-title">Setiap minggu</h2>
                            <p class="frequency-desc">Kajian kelompok dan pengasuh</p>
                        </div>
                    </div>
                    <ul class="frequency-list">
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Nashoihul Ibad</span>
                                <span class="activity-sub">Pengasuh</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Bulughul Maram</span>
                                <span class="activity-sub">KH. Chozin Dahlan</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Muhadhoroh per Kamar</span>
                                <span class="activity-sub">Setiap hari Senin ba'd Maghrib</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Tafsir al-Jalalain</span>
                                <span class="activity-sub">Setiap Kamis sore (Gus Izzuddin)</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Sholawat Burdah</span>
                                <span class="activity-sub">Sama-sama</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Maulid Diba'i</span>
                                <span class="activity-sub">Setiap Kamis ba'd Maghrib</span>
                            </div>
                        </li>
                    </ul>
                </section>

                {{-- Setiap Bulan --}}
                <section class="program-frequency" id="bulanan">
                    <div class="frequency-header">
                        <div class="frequency-icon">
                            <x-site-icon name="clock" :size="24" />
                        </div>
                        <div>
                            <h2 class="frequency-title">Setiap bulan</h2>
                            <p class="frequency-desc">Kegiatan bulanan</p>
                        </div>
                    </div>
                    <ul class="frequency-list">
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Muhadhoroh Akbar</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Khataman Al-qur'an</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Manaqib Bersama</span>
                            </div>
                        </li>
                    </ul>
                </section>

                {{-- Setiap Tahun --}}
                <section class="program-frequency" id="tahunan">
                    <div class="frequency-header">
                        <div class="frequency-icon">
                            <x-site-icon name="sparkle" :size="24" />
                        </div>
                        <div>
                            <h2 class="frequency-title">Setiap tahun</h2>
                            <p class="frequency-desc">Acara tahunan</p>
                        </div>
                    </div>
                    <ul class="frequency-list">
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Ziarah Wali Kelas Akhir</span>
                                <span class="activity-sub">3 SLTP & 3 SLTA</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Isra' Mi'raj</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Maulid Nabi</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Nuzul Qur'an</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Haflah Akhirussanah</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Haul Masyayikh</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">Hari Santri Nasional</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <span class="activity-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <div>
                                <span class="activity-label">HUT Kemerdekaan RI</span>
                            </div>
                        </li>
                    </ul>
                </section>

            </div>
        </div>
    </section>
@endsection