<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityGroup;
use App\Models\GalleryItem;
use App\Models\Greeting;
use App\Models\ManagementPeriod;
use App\Support\ManagementTree;
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
            'activities' => $this->featuredActivities(),
            'news' => array_slice($this->news(), 0, 3),
            'gallery' => $this->galleryItems(),
        ]);
    }

    public function profil(): View
    {
        $kepengurusan = ManagementPeriod::publicActive();

        return view('profil', [
            'greeting' => Greeting::query()->active()->orderBy('id')->first(),
            'values' => $this->profileValues(),
            'kepengurusan' => $kepengurusan,
            'struktur' => $kepengurusan !== null ? ManagementTree::build($kepengurusan) : null,
        ]);
    }

    public function kegiatan(): View
    {
        return view('kegiatan', [
            'programGroups' => $this->activityGroups(),
        ]);
    }

    public function galeri(): View
    {
        return view('galeri', [
            'gallery' => $this->galleryItems(),
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

    private function featuredActivities(): array
    {
        return Activity::query()
            ->featured()
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->values()
            ->map(fn (Activity $activity, int $index): array => $this->activityPayload($activity, $index + 1))
            ->all();
    }

    private function activityGroups(): array
    {
        $number = 0;

        return ActivityGroup::query()
            ->orderBy('sort')
            ->orderBy('id')
            ->with(['activities' => fn ($query) => $query->orderBy('sort')->orderBy('id')])
            ->get()
            ->values()
            ->map(fn (ActivityGroup $group, int $groupIndex): array => [
                'id' => (string) $group->slug,
                'eyebrow' => sprintf('Kelompok %02d', $groupIndex + 1),
                'title' => (string) $group->title,
                'description' => (string) $group->description,
                'activities' => $group->activities
                    ->map(function (Activity $activity) use (&$number): array {
                        return $this->activityPayload($activity, ++$number);
                    })
                    ->all(),
            ])
            ->all();
    }

    private function activityPayload(Activity $activity, int $number): array
    {
        return [
            'number' => sprintf('%02d', $number),
            'label' => (string) $activity->label,
            'title' => (string) $activity->title,
            'text' => (string) $activity->text,
            'image' => $activity->image_key,
            'media_id' => $activity->site_media_id,
        ];
    }

    private function profileValues(): array
    {
        return [
            [
                'number' => '01',
                'icon' => 'quran',
                'title' => "Kedekatan dengan Al-Qur'an",
                'text' => 'Tahfizh, setoran, dan murajaah menjadi bagian tak terpisahkan dari keseharian santri.',
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
                'excerpt' => 'Haflah akhirussanah menjadi apresiasi atas perjuangan belajar santri sepanjang tahun ajaran.',
                'body' => "Haflah Akhirussanah Ribath Roudlotul Qur'an 2025 telah berlangsung sebagai penutup tahun ajaran.\n\nHaflah menjadi momen apresiasi bagi santri atas perjuangan belajar, menghafal Al-Qur'an, dan membina akhlak sepanjang tahun, sekaligus silaturahmi keluarga besar ribath.\n\nDokumentasi aftermovie haflah tersedia di kanal YouTube resmi ribath.",
                'category' => 'Kabar',
                'date' => '2025',
                'image' => 'courtyard',
                'author' => 'Pengurus Asrama',
            ],
            [
                'slug' => 'pelantikan-pengurus-ribath-roudlotul-quran-1',
                'title' => "Pelantikan Pengurus Ribath Roudlotul Qur'an 1",
                'excerpt' => 'Pengurus baru dilantik untuk melanjutkan pelayanan pembinaan santri di ribath.',
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

    private function galleryItems(): array
    {
        return GalleryItem::query()
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->map(fn (GalleryItem $item): array => [
                'caption' => (string) $item->caption,
                'image' => $item->image_key,
                'media_id' => $item->site_media_id,
            ])
            ->all();
    }

}
