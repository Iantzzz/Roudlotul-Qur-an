<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicPageController extends Controller
{
    public function home(): View
    {
        return view('home', [
            'foundations' => $this->foundations(),
            'pillars' => $this->pillars(),
            'activities' => $this->activities(),
            'news' => array_slice($this->news(), 0, 3),
            'gallery' => $this->gallery(),
        ]);
    }

    public function profil(): View
    {
        return view('profil', [
            'values' => $this->profileValues(),
        ]);
    }

    public function kegiatan(): View
    {
        return view('kegiatan', [
            'programGroups' => $this->programGroups(),
        ]);
    }

    public function galeri(): View
    {
        return view('galeri', [
            'gallery' => $this->gallery(),
        ]);
    }

    public function berita(Request $request): View
    {
        $posts = $this->news();
        $categories = collect($posts)
            ->pluck('category')
            ->unique()
            ->map(fn (string $category) => [
                'label' => $category,
                'slug' => Str::slug($category),
            ])
            ->values()
            ->all();

        $requestedCategory = $request->query('kategori');
        $requestedCategory = is_string($requestedCategory) ? trim($requestedCategory) : '';
        $activeCategory = collect($categories)->firstWhere('slug', $requestedCategory);
        $activeCategorySlug = $activeCategory['slug'] ?? null;

        $filteredPosts = $activeCategory
            ? array_values(array_filter(
                $posts,
                fn (array $post) => Str::slug($post['category']) === $activeCategorySlug,
            ))
            : $posts;

        return view('berita.index', [
            'posts' => $filteredPosts,
            'categories' => $categories,
            'activeCategorySlug' => $activeCategorySlug,
        ]);
    }

    public function beritaShow(string $slug): View
    {
        $posts = $this->news();
        $post = collect($posts)->firstWhere('slug', $slug);

        abort_if($post === null, 404);

        $related = collect($posts)
            ->reject(fn (array $item) => $item['slug'] === $slug)
            ->sortByDesc(fn (array $item) => $item['category'] === $post['category'])
            ->take(3)
            ->values()
            ->all();

        return view('berita.show', [
            'post' => $post,
            'related' => $related,
        ]);
    }

    public function pendaftaran(): View
    {
        return view('pendaftaran');
    }

    public function kontak(): View
    {
        return view('kontak');
    }

    private function foundations(): array
    {
        return [
            [
                'icon' => 'quran',
                'title' => "Al-Qur'an",
                'text' => 'Menempatkan pembelajaran dan kecintaan kepada Al-Qur’an sebagai fondasi utama.',
            ],
            [
                'icon' => 'heart',
                'title' => 'Akhlak',
                'text' => 'Membiasakan adab, tanggung jawab, dan sikap saling menghormati dalam keseharian.',
            ],
            [
                'icon' => 'book',
                'title' => 'Ilmu',
                'text' => 'Membuka ruang untuk belajar, bertanya, dan memperdalam ilmu secara terarah.',
            ],
            [
                'icon' => 'users',
                'title' => 'Kebersamaan',
                'text' => 'Menumbuhkan persaudaraan dan semangat saling mendukung di lingkungan asrama.',
            ],
        ];
    }

    private function pillars(): array
    {
        return [
            [
                'number' => '01',
                'icon' => 'quran',
                'title' => "Tahfizh Al-Qur'an",
                'text' => 'Contoh fokus pembinaan untuk membangun kedekatan santri dengan Al-Qur’an melalui hafalan dan murajaah.',
            ],
            [
                'number' => '02',
                'icon' => 'heart',
                'title' => 'Pembinaan Akhlak',
                'text' => 'Contoh pendampingan karakter agar santri terbiasa beradab, disiplin, dan bertanggung jawab.',
            ],
            [
                'number' => '03',
                'icon' => 'home',
                'title' => 'Kehidupan Asrama',
                'text' => 'Contoh suasana kebersamaan yang mendukung kemandirian dan kepedulian antarpenghuni.',
            ],
            [
                'number' => '04',
                'icon' => 'book',
                'title' => 'Ilmu Diniyah',
                'text' => 'Contoh ruang belajar untuk memperdalam pemahaman agama dengan bimbingan yang terarah.',
            ],
        ];
    }

    private function activities(): array
    {
        return [
            [
                'number' => '01',
                'label' => 'Contoh kegiatan',
                'title' => "Halaqah Al-Qur'an",
                'text' => 'Contoh kegiatan belajar, menyimak, dan mengulang hafalan bersama.',
                'image' => 'quran_open',
            ],
            [
                'number' => '02',
                'label' => 'Contoh kegiatan',
                'title' => 'Kajian Ilmu Diniyah',
                'text' => 'Contoh forum kajian untuk membaca, memahami, dan mendiskusikan ilmu agama.',
                'image' => 'library',
            ],
            [
                'number' => '03',
                'label' => 'Contoh kegiatan',
                'title' => 'Pembinaan Akhlak',
                'text' => 'Contoh pembiasaan adab dan karakter melalui pendampingan sehari-hari.',
                'image' => 'calligraphy_gold',
            ],
            [
                'number' => '04',
                'label' => 'Contoh kegiatan',
                'title' => 'Kebersamaan Santri',
                'text' => 'Contoh kegiatan yang membangun kerja sama, kepedulian, dan rasa memiliki.',
                'image' => 'courtyard',
            ],
        ];
    }

    private function profileValues(): array
    {
        return [
            [
                'number' => '01',
                'icon' => 'quran',
                'title' => "Kedekatan dengan Al-Qur'an",
                'text' => "Contoh arah nilai yang menempatkan pembelajaran dan kecintaan kepada Al-Qur'an sebagai bagian dari keseharian.",
            ],
            [
                'number' => '02',
                'icon' => 'heart',
                'title' => 'Adab dan Tanggung Jawab',
                'text' => 'Contoh arah nilai untuk membiasakan sikap hormat, disiplin, dan tanggung jawab.',
            ],
            [
                'number' => '03',
                'icon' => 'book',
                'title' => 'Semangat Belajar',
                'text' => 'Contoh arah nilai untuk menumbuhkan rasa ingin tahu dan kemauan memperdalam ilmu.',
            ],
            [
                'number' => '04',
                'icon' => 'users',
                'title' => 'Kebersamaan',
                'text' => 'Contoh arah nilai untuk merawat kepedulian dan saling mendukung di lingkungan asrama.',
            ],
        ];
    }

    private function programGroups(): array
    {
        return [
            [
                'id' => 'ibadah',
                'eyebrow' => 'Kelompok 01',
                'title' => 'Ibadah',
                'description' => 'Contoh pengelompokan kegiatan yang berkaitan dengan pembiasaan dan pendampingan ibadah. Program resmi belum tersedia.',
                'activities' => [
                    [
                        'number' => '01',
                        'label' => 'Contoh program',
                        'title' => 'Pembiasaan Ibadah',
                        'text' => 'Contoh gambaran kegiatan untuk merawat kebiasaan ibadah dalam keseharian santri.',
                        'image' => 'mosque_light',
                    ],
                    [
                        'number' => '02',
                        'label' => 'Contoh program',
                        'title' => 'Pendampingan Ibadah',
                        'text' => 'Contoh gambaran pendampingan agar santri memahami dan menjalankan ibadah dengan tertib.',
                        'image' => 'quran_detail',
                    ],
                ],
            ],
            [
                'id' => 'pendidikan',
                'eyebrow' => 'Kelompok 02',
                'title' => 'Pendidikan',
                'description' => 'Contoh pengelompokan kegiatan pembelajaran Al-Qur’an dan ilmu diniyah. Nama serta isi program resmi akan ditambahkan kemudian.',
                'activities' => [
                    [
                        'number' => '03',
                        'label' => 'Contoh program',
                        'title' => "Halaqah Al-Qur'an",
                        'text' => 'Contoh gambaran kegiatan belajar, menyimak, dan mengulang hafalan bersama.',
                        'image' => 'quran_open',
                    ],
                    [
                        'number' => '04',
                        'label' => 'Contoh program',
                        'title' => 'Kajian Ilmu Diniyah',
                        'text' => 'Contoh gambaran forum kajian untuk membaca, memahami, dan mendiskusikan ilmu agama.',
                        'image' => 'library',
                    ],
                ],
            ],
            [
                'id' => 'pembinaan-karakter',
                'eyebrow' => 'Kelompok 03',
                'title' => 'Pembinaan Karakter',
                'description' => 'Contoh pengelompokan kegiatan yang menekankan adab, tanggung jawab, dan kebersamaan. Susunan resmi belum tersedia.',
                'activities' => [
                    [
                        'number' => '05',
                        'label' => 'Contoh program',
                        'title' => 'Pembinaan Akhlak',
                        'text' => 'Contoh gambaran pembiasaan adab dan karakter melalui pendampingan sehari-hari.',
                        'image' => 'calligraphy_gold',
                    ],
                    [
                        'number' => '06',
                        'label' => 'Contoh program',
                        'title' => 'Kebersamaan Santri',
                        'text' => 'Contoh gambaran kegiatan yang membangun kerja sama, kepedulian, dan rasa memiliki.',
                        'image' => 'courtyard',
                    ],
                ],
            ],
            [
                'id' => 'kemandirian',
                'eyebrow' => 'Kelompok 04',
                'title' => 'Kemandirian & Keterampilan',
                'description' => 'Contoh pengelompokan kegiatan pendukung kemandirian dan keterampilan santri. Program resmi akan diumumkan kemudian.',
                'activities' => [
                    [
                        'number' => '07',
                        'label' => 'Contoh program',
                        'title' => 'Kemandirian Santri',
                        'text' => 'Contoh gambaran pembiasaan untuk merawat kemandirian dalam kehidupan asrama.',
                        'image' => 'books_row',
                    ],
                    [
                        'number' => '08',
                        'label' => 'Contoh program',
                        'title' => 'Keterampilan Pendukung',
                        'text' => 'Contoh gambaran ruang belajar keterampilan pendukung yang akan disesuaikan dengan program resmi.',
                        'image' => 'pattern_blue',
                    ],
                ],
            ],
        ];
    }

    private function news(): array
    {
        return [
            [
                'slug' => 'contoh-pengumuman-jadwal-kegiatan-asrama',
                'title' => 'Contoh pengumuman jadwal kegiatan asrama',
                'excerpt' => 'Contoh kartu pengumuman untuk menggambarkan tampilan daftar berita. Jadwal resmi belum tersedia.',
                'body' => "Teks ini adalah contoh isi pengumuman. Jadwal kegiatan asrama yang sebenarnya belum tersedia untuk dipublikasikan.\n\nBagian ini akan diganti dengan pengumuman resmi dari pengurus, termasuk tanggal, waktu, dan ketentuan kegiatan.\n\nContoh ini tidak menyampaikan agenda atau peristiwa nyata.",
                'category' => 'Pengumuman',
                'date' => 'Tanggal menyusul',
                'image' => 'mosque_light',
                'author' => 'Contoh penulis',
            ],
            [
                'slug' => 'contoh-pengumuman-informasi-pendaftaran',
                'title' => 'Contoh pengumuman informasi pendaftaran',
                'excerpt' => 'Contoh pengumuman untuk menggambarkan informasi pendaftaran. Tanggal dan biaya resmi belum tersedia.',
                'body' => "Teks ini adalah contoh isi pengumuman pendaftaran. Tanggal, persyaratan, dan biaya resmi belum tersedia.\n\nHalaman ini hanya menggambarkan bagaimana informasi pendaftaran akan ditampilkan. Seluruh data resmi akan ditambahkan pada tahap berikutnya.\n\nTidak ada angka, tanggal, atau ketentuan pendaftaran yang berlaku dalam contoh ini.",
                'category' => 'Pengumuman',
                'date' => 'Tanggal menyusul',
                'image' => 'courtyard',
                'author' => 'Contoh penulis',
            ],
            [
                'slug' => 'contoh-artikel-pembiasaan-belajar-al-quran',
                'title' => "Contoh artikel tentang pembiasaan belajar Al-Qur'an",
                'excerpt' => "Contoh ringkasan artikel tentang pembiasaan belajar Al-Qur'an. Seluruh isi masih berupa contoh.",
                'body' => "Artikel contoh ini membahas pentingnya pembiasaan belajar Al-Qur’an secara bertahap dan konsisten. Pembahasan bersifat umum dan tidak mewakili program resmi asrama.\n\nKebiasaan belajar dapat dibangun melalui waktu yang teratur, suasana yang mendukung, dan pendampingan yang sesuai.\n\nIsi lengkap artikel akan diganti setelah materi resmi tersedia.",
                'category' => 'Artikel',
                'date' => 'Tanggal menyusul',
                'image' => 'quran_open',
                'author' => 'Contoh penulis',
            ],
            [
                'slug' => 'contoh-artikel-pendampingan-adab',
                'title' => 'Contoh artikel tentang pendampingan adab',
                'excerpt' => 'Contoh artikel untuk menggambarkan pembahasan pendampingan adab. Bukan materi resmi asrama.',
                'body' => "Artikel contoh ini membahas pendampingan adab sebagai bagian dari pembiasaan sehari-hari. Uraian berikut bersifat umum dan bukan pernyataan resmi Roudlotul Qur’an.\n\nPendampingan dapat dimulai dari keteladanan, komunikasi yang jelas, dan konsistensi dalam lingkungan belajar.\n\nArtikel resmi akan ditambahkan kemudian.",
                'category' => 'Artikel',
                'date' => 'Tanggal menyusul',
                'image' => 'calligraphy_gold',
                'author' => 'Contoh penulis',
            ],
            [
                'slug' => 'contoh-kabar-kegiatan-kebersamaan',
                'title' => 'Contoh kabar kegiatan kebersamaan santri',
                'excerpt' => 'Contoh kartu kabar untuk menggambarkan kegiatan kebersamaan. Bukan laporan peristiwa nyata.',
                'body' => "Teks ini adalah contoh kabar kegiatan kebersamaan. Kegiatan yang diuraikan bukan peristiwa nyata dan tidak mewakili agenda resmi asrama.\n\nBagian ini disiapkan untuk menggambarkan format berita kegiatan, termasuk judul, foto, dan ringkasan.\n\nKabar resmi akan menggantikan contoh ini pada tahap berikutnya.",
                'category' => 'Kabar',
                'date' => 'Tanggal menyusul',
                'image' => 'courtyard',
                'author' => 'Contoh penulis',
            ],
            [
                'slug' => 'contoh-kabar-kegiatan-belajar-bersama',
                'title' => 'Contoh kabar kegiatan belajar bersama',
                'excerpt' => 'Contoh kabar kegiatan belajar bersama untuk keperluan tampilan. Tidak ada peristiwa nyata.',
                'body' => "Teks ini adalah contoh kabar kegiatan belajar bersama. Tidak ada kegiatan nyata yang dilaporkan melalui contoh ini.\n\nFormat ini akan digunakan untuk memuat informasi kegiatan resmi beserta keterangan waktu dan dokumentasi.\n\nSeluruh isi masih sementara dan akan diperbarui.",
                'category' => 'Kabar',
                'date' => 'Tanggal menyusul',
                'image' => 'library',
                'author' => 'Contoh penulis',
            ],
        ];
    }

    private function gallery(): array
    {
        return [
            [
                'image' => 'calligraphy_gold',
                'caption' => 'Detail kaligrafi dan ornamen',
            ],
            [
                'image' => 'quran_detail',
                'caption' => "Naskah Al-Qur'an dengan iluminasi",
            ],
            [
                'image' => 'courtyard',
                'caption' => 'Suasana halaman bernuansa islami',
            ],
            [
                'image' => 'library',
                'caption' => 'Ruang baca dan koleksi kitab',
            ],
            [
                'image' => 'pattern_blue',
                'caption' => 'Motif geometris pada dinding',
            ],
            [
                'image' => 'mosque_light',
                'caption' => 'Interior masjid dengan kaligrafi',
            ],
        ];
    }
}
