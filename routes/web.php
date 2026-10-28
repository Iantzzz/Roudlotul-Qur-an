<?php

use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\Route;

Route::controller(PublicPageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/profil', 'profil')->name('profil');
    Route::get('/kegiatan', 'kegiatan')->name('kegiatan');
    Route::get('/galeri', 'galeri')->name('galeri');
    Route::get('/berita', 'berita')->name('berita');
    Route::get('/berita/{slug}', 'beritaShow')->name('berita.show');
    Route::get('/pendaftaran', 'pendaftaran')->name('pendaftaran');
    Route::get('/kontak', 'kontak')->name('kontak');
});
