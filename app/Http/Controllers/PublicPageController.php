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
                'text' => 'Pembinaan hafalan dan murajaah menjadi denyut utama keseharian santri.',
            ],
            [
                'number' => '02',
                'icon' => 'heart',
                'title' => 'Pembinaan Akhlak',
                'text' => 'Adab, disiplin, dan tanggung jawab dibiasakan melalui pendampingan sehari-hari.',
            ],
            [
                'number' => '03',
                'icon' => 'home',
                'title' => 'Kehidupan Asrama',
                'text' => 'Kebersamaan antarkamar menumbuhkan kemandirian, kekompakan, dan ukhuwah.',
            ],
            [
                'number' => '04',
                'icon' => 'book',
                'title' => 'Ilmu Diniyah',
                'text' => 'Kajian kitab dan tafsir memperdalam pemahaman agama secara terarah.',
            ],
        ];
    }

    private function activities(): array
    {
        return [
            [
                'number' => '01',
                'label' => 'Kegiatan harian',
                'title' => 'Tahfizh & Setoran Hafalan',
                'text' => "Santri menghafal, menyetorkan, dan mengulang hafalan Al-Qur'an dengan pendampingan.",
                'image' => 'quran_open',
            ],
            [
                'number' => '02',
                'label' => 'Kajian rutin',
                'title' => 'Kajian Tafsir Jalalain',
                'text' => "Kajian tafsir bersama KH Drs. Cholil Dahlan yang telah menempuh surah Al-Ma'idah, Al-A'raf, dan Al-Anfal.",
                'image' => 'library',
            ],
            [
                'number' => '03',
                'label' => 'Kegiatan tahunan',
                'title' => "Ta'dhim Maulid Nabi",
                'text' => "Peringatan Maulid Nabi dengan bacaan sholawat, termasuk Diba', untuk menumbuhkan cinta kepada Rasulullah.",
                'image' => 'mosque_light',
            ],
            [
                'number' => '04',
                'label' => 'Kegiatan asrama',
                'title' => 'Liga Kamar',
                'text' => 'Kompetisi antarkamar yang menumbuhkan sportivitas, kekompakan, dan ukhuwah antarsantri.',
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
                'text' => "Tahfizh, setoran, dan murajaah menjadi bagian tak terpisahkan dari keseharian santri.",
            ],
            [
                'number' => '02',
                'icon' => 'heart',
                'title' => 'Adab & Ukhuwah',
                'text' => 'Membiasakan menjaga lisan, sikap, dan perasaan sesama dalam kehidupan asrama.',
            ],
            [
                'number' => '03',
                'icon' => 'book',
                'title' => 'Disiplin & Kemandirian',
                'text' => 'Santri didorong tumbuh disiplin, mandiri, dan berakhlak melalui program pembinaan.',
            ],
            [
                'number' => '04',
                'icon' => 'users',
                'title' => 'Semangat Belajar & Mengabdi',
                'text' => 'Mengaji dengan hati, berjuang dengan aksi, dan mengabdi kepada sesama serta negeri.',
            ],
        ];
    }

    private function programGroups(): array
    {
        return [
            [
                'id' => 'ibadah',
                'eyebrow' => 'Kelompok 01',
                'title' => 'Ibadah & Tahfizh',
                'description' => "Pembiasaan ibadah dan pembinaan hafalan Al-Qur'an menjadi denyut utama keseharian santri.",
                'activities' => [
                    [
                        'number' => '01',
                        'label' => 'Kegiatan harian',
                        'title' => 'Tahfizh & Setoran Hafalan',
                        'text' => "Santri menghafal, menyetorkan, dan mengulang hafalan Al-Qur'an secara rutin dengan pendampingan.",
                        'image' => 'quran_open',
                    ],
                    [
                        'number' => '02',
                        'label' => 'Momen santri',
                        'title' => "Khataman Al-Qur'an",
                        'text' => 'Khataman menandai tahapan hafalan dan belajar yang telah diselesaikan santri.',
                        'image' => 'quran_detail',
                    ],
                    [
                        'number' => '03',
                        'label' => 'Kegiatan tahunan',
                        'title' => "Ta'dhim Maulid Nabi",
                        'text' => "Peringatan Maulid Nabi dengan bacaan sholawat, termasuk Diba', menumbuhkan cinta dan teladan akhlak Rasulullah.",
                        'image' => 'mosque_light',
                    ],
                ],
            ],
            [
                'id' => 'pendidikan',
                'eyebrow' => 'Kelompok 02',
                'title' => 'Pendidikan & Kajian',
                'description' => 'Kajian rutin dan pendalaman kitab membangun pemahaman agama yang terarah.',
                'activities' => [
                    [
                        'number' => '04',
                        'label' => 'Kajian rutin',
                        'title' => 'Kajian Tafsir Jalalain',
                        'text' => "Kajian tafsir bersama KH Drs. Cholil Dahlan, telah menempuh surah Al-Ma'idah, Al-A'raf, dan Al-Anfal.",
                        'image' => 'library',
                    ],
                    [
                        'number' => '05',
                        'label' => 'Kajian kitab',
                        'title' => 'Kajian Kitab Klasik',
                        'text' => "Pendalaman kitab seperti Nadhom Aqidatul Awam dan Nashaih al-'Ibad bersama santri.",
                        'image' => 'books_row',
                    ],
                    [
                        'number' => '06',
                        'label' => 'Acara tahunan',
                        'title' => 'Haflah Akhirussanah',
                        'text' => 'Haflah penutup tahun ajaran menjadi apresiasi atas perjuangan belajar santri sepanjang tahun.',
                        'image' => 'courtyard',
                    ],
                ],
            ],
            [
                'id' => 'pembinaan-karakter',
                'eyebrow' => 'Kelompok 03',
                'title' => 'Pembinaan Karakter & Kebersamaan',
                'description' => 'Program pembinaan dan kegiatan kebersamaan membentuk disiplin, kemandirian, dan ukhuwah.',
                'activities' => [
                    [
                        'number' => '07',
                        'label' => 'Pembinaan',
                        'title' => 'LAPSIDU & P2KS',
                        'text' => 'Santri mengikuti latihan dasar kepemimpinan dan pembinaan karakter untuk tumbuh disiplin, mandiri, dan berakhlak.',
                        'image' => 'calligraphy_gold',
                    ],
                    [
                        'number' => '08',
                        'label' => 'Kegiatan asrama',
                        'title' => 'Liga Kamar',
                        'text' => 'Kompetisi antarkamar yang menumbuhkan sportivitas, kekompakan, dan ukhuwah antarsantri.',
                        'image' => 'pattern_blue',
                    ],
                    [
                        'number' => '09',
                        'label' => 'Peringatan tahunan',
                        'title' => 'Hari Santri & Hari Guru Nasional',
                        'text' => 'Santri ikut memperingati hari besar nasional sebagai bentuk syukur dan pengabdian.',
                        'image' => 'quran_manuscript',
                    ],
                ],
            ],
            [
                'id' => 'silaturahmi',
                'eyebrow' => 'Kelompok 04',
                'title' => 'Silaturahmi & Tradisi',
                'description' => 'Tradisi dan perjalanan rohani merawat kebersamaan dengan sesama, ulama, dan para wali.',
                'activities' => [
                    [
                        'number' => '10',
                        'label' => 'Tradisi',
                        'title' => 'Ziarah Wali & Manaqib',
                        'text' => 'Ziarah ke makam para wali dan pembacaan manaqib sebagai tradisi penumbuh keberkahan.',
                        'image' => 'hero',
                    ],
                    [
                        'number' => '11',
                        'label' => 'Program',
                        'title' => 'Umrah',
                        'text' => 'Kesempatan menunaikan ibadah umrah bersama warga ribath.',
                        'image' => 'mosque_light',
                    ],
                    [
                        'number' => '12',
                        'label' => 'Tradisi',
                        'title' => "Ru'yatul Hilal",
                        'text' => 'Santri mengikuti tradisi rukyat hilal menyambut bulan-bulan Hijriah.',
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
                'slug' => 'hari-santri-nasional-2026',
                'title' => 'Selamat Hari Santri Nasional 2026',
                'excerpt' => 'Santri bukan hanya tentang sarung dan peci, tetapi tentang perjuangan menuntut ilmu, menjaga adab, dan mengabdi kepada negeri.',
                'body' => "Dari pesantren, tumbuh generasi yang berilmu, berakhlak, dan siap membawa keberkahan bagi bangsa. Itulah pesan yang dibagikan ribath pada peringatan Hari Santri Nasional 2026.\n\n\"Jadilah santri yang mengaji dengan hati, berjuang dengan aksi, dan mengabdi tanpa henti\" — pesan yang menjadi pengingat bagi seluruh santri untuk terus meneladani semangat para ulama.\n\nSeluruh santri dan pengurus Ribath Roudlotul Qur'an 1 mengucapkan selamat Hari Santri Nasional 2026.",
                'category' => 'Kabar',
                'date' => '22 Oktober 2026',
                'image' => 'quran_open',
                'author' => 'Pengurus Asrama',
            ],
            [
                'slug' => 'takdhim-maulid-nabi-1448-h',
                'title' => "Ta'dhim Maulid Nabi 1448 H",
                'excerpt' => "Ta'dhim Maulid Nabi dengan bacaan sholawat Diba' menjadi momentum menumbuhkan cinta kepada Rasulullah SAW.",
                'body' => "Asrama Ribath Roudlotul Qur'an 1 mengadakan takdhim Maulid Nabi 1448 H di lingkungan Pondok Pesantren Darul Ulum.\n\nMemperingati Maulid Nabi bukan sekadar mengenang kelahiran Rasulullah SAW, tetapi menjadi momentum untuk menumbuhkan cinta, memperbanyak shalawat, dan meneladani akhlak mulia beliau dalam kehidupan sehari-hari.\n\nSemoga dengan cinta kepada Rasulullah SAW, hati kita semakin dekat kepada Allah, langkah kita senantiasa berada dalam kebaikan, dan kita kelak mendapat syafaat beliau di hari kiamat.",
                'category' => 'Kabar',
                'date' => 'Rabiul Awwal 1448 H',
                'image' => 'mosque_light',
                'author' => 'Pengurus Asrama',
            ],
            [
                'slug' => 'haflah-akhirussanah-2025',
                'title' => "Haflah Akhirussanah Ribath Roudlotul Qur'an 2025",
                'excerpt' => "Haflah akhirussanah menjadi apresiasi atas perjuangan belajar santri sepanjang tahun ajaran.",
                'body' => "Haflah Akhirussanah Ribath Roudlotul Qur'an 2025 telah berlangsung sebagai penutup tahun ajaran.\n\nHaflah menjadi momen apresiasi bagi santri atas perjuangan belajar, menghafal Al-Qur'an, dan membina akhlak sepanjang tahun, sekaligus silaturahmi keluarga besar ribath.\n\nDokumentasi aftermovie haflah tersedia di kanal YouTube resmi ribath.",
                'category' => 'Kabar',
                'date' => '2025',
                'image' => 'courtyard',
                'author' => 'Pengurus Asrama',
            ],
            [
                'slug' => 'pelantikan-pengurus-ribath-roudlotul-quran-1',
                'title' => "Pelantikan Pengurus Ribath Roudlotul Qur'an 1",
                'excerpt' => "Pengurus baru dilantik untuk melanjutkan pelayanan pembinaan santri di ribath.",
                'body' => "Pelantikan pengurus Ribath Roudlotul Qur'an 1 telah dilaksanakan untuk periode kepemimpinan yang baru.\n\nKepengurusan menjadi bagian penting dalam merawat keseharian santri, mulai dari pembinaan hafalan, kegiatan asrama, hingga penyelenggaraan berbagai acara tahunan.\n\nSemoga pengurus yang dilantik diberi kelancaran dalam mengabdi dan menjadi rahmat bagi seluruh warga ribath.",
                'category' => 'Kabar',
                'date' => '2025',
                'image' => 'calligraphy_gold',
                'author' => 'Pengurus Asrama',
            ],
            [
                'slug' => 'nadhom-aqidatul-awam-santri',
                'title' => "Nadhom Aqidatul Awam oleh Santri Roudlotul Qur'an",
                'excerpt' => 'Santri memperdalam aqidah melalui nadhom Aqidatul Awam di bawah bimbingan asatidz.',
                'body' => "Santri Ribath Roudlotul Qur'an menampilkan pendalaman nadhom Aqidatul Awam, kitab pengantar ilmu aqidah yang diperdalam melalui hafalan dan pemahaman bersama asatidz.\n\nPendalaman kitab klasik seperti ini menjadi bagian dari kurikulum diniyah yang menemani pembinaan tahfizh Al-Qur'an.\n\nRekaman kegiatan ini tersedia di kanal YouTube resmi ribath.",
                'category' => 'Kabar',
                'date' => '2025',
                'image' => 'quran_detail',
                'author' => 'Pengurus Asrama',
            ],
            [
                'slug' => 'kajian-tafsir-jalalain-rutin',
                'title' => 'Kajian Tafsir Jalalain Berlangsung Rutin',
                'excerpt' => "Kajian Tafsir Jalalain bersama KH Drs. Cholil Dahlan telah menempuh surah Al-Ma'idah, Al-A'raf, dan Al-Anfal.",
                'body' => "Kajian Tafsir Jalalain bersama KH Drs. Cholil Dahlan (Buya Cholil) berlangsung rutin di lingkungan ribath dan telah terekam dalam banyak sesi.\n\nPerjalanan kajian telah menempuh berbagai bagian Al-Qur'an, antara lain surah Al-Ma'idah, Al-A'raf, dan Al-Anfal, dibawakan dengan bahasa yang mudah dipahami santri.\n\nSeluruh rekaman kajian dapat disimak melalui kanal YouTube resmi ribath.",
                'category' => 'Artikel',
                'date' => '2024–2026',
                'image' => 'library',
                'author' => 'Pengurus Asrama',
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
