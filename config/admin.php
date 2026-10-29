<?php

/**
 * Konfigurasi akun admin Filament untuk produksi.
 *
 * Nilainya diambil dari environment dashboard (ADMIN_EMAIL, ADMIN_PASSWORD,
 * ADMIN_NAME) dan TIDAK boleh diletakkan di file repo. config()/env tidak
 * diketik cache (tidak ada config:cache pada deployment) sehingga perubahan
 * variabel dashboard berlaku setelah redeploy.
 */

return [
    'email' => env('ADMIN_EMAIL', ''),
    'password' => env('ADMIN_PASSWORD', ''),
    'name' => env('ADMIN_NAME', 'Admin'),
];
