<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityGroup;
use App\Models\GalleryItem;
use Illuminate\Database\Seeder;

/**
 * Mengisi konten situs (kegiatan & galeri) dari data yang semula hardcode
 * di PublicPageController, sehingga tampilan publik tidak berubah setelah
 * halaman-halaman dibuat membaca database.
 *
 * Idempotent: tabel yang sudah terisi dilewati, jadi dijalankan ulang
 * tidak membuat duplikat. Sambutan (tabel greetings) sengaja TIDAK
 * di-seed — section Sambutan memang tersembunyi sampai admin membuatnya
 * dari panel admin.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        if (ActivityGroup::query()->doesntExist()) {
            $this->seedActivityGroups();
        }

        if (Activity::query()->featured()->doesntExist()) {
            $this->seedFeaturedActivities();
        }

        if (GalleryItem::query()->doesntExist()) {
            $this->seedGalleryItems();
        }
    }

    private function seedActivityGroups(): void
    {
        $groups = [
            [
                'slug' => 'ibadah',
                'title' => 'Ibadah & Tahfizh',
                'description' => "Pembiasaan ibadah dan pembinaan hafalan Al-Qur'an menjadi denyut utama keseharian santri.",
                'activities' => [
                    [
                        'label' => 'Kegiatan harian',
                        'title' => 'Tahfizh & Setoran Hafalan',
                        'text' => "Santri menghafal, menyetorkan, dan mengulang hafalan Al-Qur'an secara rutin dengan pendampingan.",
                        'image' => 'quran_open',
                    ],
                    [
                        'label' => 'Momen santri',
                        'title' => "Khataman Al-Qur'an",
                        'text' => 'Khataman menandai tahapan hafalan dan belajar yang telah diselesaikan santri.',
                        'image' => 'quran_detail',
                    ],
                    [
                        'label' => 'Kegiatan tahunan',
                        'title' => "Ta'dhim Maulid Nabi",
                        'text' => "Peringatan Maulid Nabi dengan bacaan sholawat, termasuk Diba', menumbuhkan cinta dan teladan akhlak Rasulullah.",
                        'image' => 'mosque_light',
                    ],
                ],
            ],
            [
                'slug' => 'pendidikan',
                'title' => 'Pendidikan & Kajian',
                'description' => 'Kajian rutin dan pendalaman kitab membangun pemahaman agama yang terarah.',
                'activities' => [
                    [
                        'label' => 'Kajian rutin',
                        'title' => 'Kajian Tafsir Jalalain',
                        'text' => "Kajian tafsir bersama KH Drs. Cholil Dahlan, telah menempuh surah Al-Ma'idah, Al-A'raf, dan Al-Anfal.",
                        'image' => 'library',
                    ],
                    [
                        'label' => 'Kajian kitab',
                        'title' => 'Kajian Kitab Klasik',
                        'text' => "Pendalaman kitab seperti Nadhom Aqidatul Awam dan Nashaih al-'Ibad bersama santri.",
                        'image' => 'books_row',
                    ],
                    [
                        'label' => 'Acara tahunan',
                        'title' => 'Haflah Akhirussanah',
                        'text' => 'Haflah penutup tahun ajaran menjadi apresiasi atas perjuangan belajar santri sepanjang tahun.',
                        'image' => 'courtyard',
                    ],
                ],
            ],
            [
                'slug' => 'pembinaan-karakter',
                'title' => 'Pembinaan Karakter & Kebersamaan',
                'description' => 'Program pembinaan dan kegiatan kebersamaan membentuk disiplin, kemandirian, dan ukhuwah.',
                'activities' => [
                    [
                        'label' => 'Pembinaan',
                        'title' => 'LAPSIDU & P2KS',
                        'text' => 'Santri mengikuti latihan dasar kepemimpinan dan pembinaan karakter untuk tumbuh disiplin, mandiri, dan berakhlak.',
                        'image' => 'calligraphy_gold',
                    ],
                    [
                        'label' => 'Kegiatan asrama',
                        'title' => 'Liga Kamar',
                        'text' => 'Kompetisi antarkamar yang menumbuhkan sportivitas, kekompakan, dan ukhuwah antarsantri.',
                        'image' => 'pattern_blue',
                    ],
                    [
                        'label' => 'Peringatan tahunan',
                        'title' => 'Hari Santri & Hari Guru Nasional',
                        'text' => 'Santri ikut memperingati hari besar nasional sebagai bentuk syukur dan pengabdian.',
                        'image' => 'quran_manuscript',
                    ],
                ],
            ],
            [
                'slug' => 'silaturahmi',
                'title' => 'Silaturahmi & Tradisi',
                'description' => 'Tradisi dan perjalanan rohani merawat kebersamaan dengan sesama, ulama, dan para wali.',
                'activities' => [
                    [
                        'label' => 'Tradisi',
                        'title' => 'Ziarah Wali & Manaqib',
                        'text' => 'Ziarah ke makam para wali dan pembacaan manaqib sebagai tradisi penumbuh keberkahan.',
                        'image' => 'hero',
                    ],
                    [
                        'label' => 'Program',
                        'title' => 'Umrah',
                        'text' => 'Kesempatan menunaikan ibadah umrah bersama warga ribath.',
                        'image' => 'mosque_light',
                    ],
                    [
                        'label' => 'Tradisi',
                        'title' => "Ru'yatul Hilal",
                        'text' => 'Santri mengikuti tradisi rukyat hilal menyambut bulan-bulan Hijriah.',
                        'image' => 'pattern_blue',
                    ],
                ],
            ],
        ];

        foreach ($groups as $groupIndex => $group) {
            $created = ActivityGroup::create([
                'title' => $group['title'],
                'slug' => $group['slug'],
                'description' => $group['description'],
                'sort' => $groupIndex + 1,
            ]);

            foreach ($group['activities'] as $activityIndex => $activity) {
                Activity::create([
                    'activity_group_id' => $created->getKey(),
                    'label' => $activity['label'],
                    'title' => $activity['title'],
                    'text' => $activity['text'],
                    'image_key' => $activity['image'],
                    'sort' => $activityIndex + 1,
                ]);
            }
        }
    }

    private function seedFeaturedActivities(): void
    {
        $featured = [
            [
                'label' => 'Kegiatan harian',
                'title' => 'Tahfizh & Setoran Hafalan',
                'text' => "Santri menghafal, menyetorkan, dan mengulang hafalan Al-Qur'an dengan pendampingan.",
                'image' => 'quran_open',
            ],
            [
                'label' => 'Kajian rutin',
                'title' => 'Kajian Tafsir Jalalain',
                'text' => "Kajian tafsir bersama KH Drs. Cholil Dahlan yang telah menempuh surah Al-Ma'idah, Al-A'raf, dan Al-Anfal.",
                'image' => 'library',
            ],
            [
                'label' => 'Kegiatan tahunan',
                'title' => "Ta'dhim Maulid Nabi",
                'text' => "Peringatan Maulid Nabi dengan bacaan sholawat, termasuk Diba', untuk menumbuhkan cinta kepada Rasulullah.",
                'image' => 'mosque_light',
            ],
            [
                'label' => 'Kegiatan asrama',
                'title' => 'Liga Kamar',
                'text' => 'Kompetisi antarkamar yang menumbuhkan sportivitas, kekompakan, dan ukhuwah antarsantri.',
                'image' => 'courtyard',
            ],
        ];

        foreach ($featured as $index => $activity) {
            Activity::create([
                'activity_group_id' => null,
                'label' => $activity['label'],
                'title' => $activity['title'],
                'text' => $activity['text'],
                'image_key' => $activity['image'],
                'sort' => $index + 1,
                'is_featured' => true,
            ]);
        }
    }

    private function seedGalleryItems(): void
    {
        $items = [
            ['image' => 'foto_1', 'caption' => 'Doa restu para asatidz'],
            ['image' => 'foto_3', 'caption' => 'Salat berjamaah di masjid pesantren'],
            ['image' => 'img_20240505_070755', 'caption' => 'Apel pagi di koridor asrama'],
            ['image' => 'img_20240523_184853', 'caption' => 'Sholawat dan doa bersama'],
            ['image' => 'img_20240523_184915', 'caption' => 'Pembinaan kebersamaan di aula'],
            ['image' => 'img_20240610_181356', 'caption' => 'Majelis kajian di aula'],
            ['image' => 'img_20240618_164445', 'caption' => "Belajar membaca Al-Qur'an"],
            ['image' => 'img_20240620_182642', 'caption' => 'Pengajian bersama di aula besar'],
            ['image' => 'img_20240621_204710', 'caption' => 'Belajar malam di ruang kelas'],
            ['image' => 'img_20240707_112042', 'caption' => 'Silaturahmi bersama wali santri'],
            ['image' => 'img_20240712_190401', 'caption' => 'Foto bersama di teras asrama'],
            ['image' => 'img_20241009_053832', 'caption' => 'Simak kitab bersama asatidz'],
            ['image' => 'img_20241009_065402', 'caption' => 'Makan bersama saat pembinaan'],
            ['image' => 'img_20241130_203337', 'caption' => 'Belajar kelompok di kelas'],
            ['image' => 'img_20260209_054845', 'caption' => 'Setoran dan murajaah kitab'],
            ['image' => 'img_20260209_070559', 'caption' => 'Apel pagi santri sekolah'],
            ['image' => 'img_20260212_070230', 'caption' => 'Barisan apel di masjid'],
            ['image' => 'img_20260212_070701', 'caption' => 'Hadiah bagi santri berprestasi'],
            ['image' => 'img_20260212_070752', 'caption' => 'Penghargaan di acara malam'],
            ['image' => 'img_20260214_193838', 'caption' => 'Ujian lisan bersama asatidz'],
            ['image' => 'img_20260214_194132', 'caption' => 'Pembacaan kitab di mimbar'],
            ['image' => 'img_20260217_202013', 'caption' => 'Panitia acara keagamaan'],
            ['image' => 'img_20260217_202029', 'caption' => 'Penyerahan brosur program'],
            ['image' => 'img_20260217_203858', 'caption' => 'Takzim dalam acara keagamaan'],
            ['image' => 'img_20260723_194642', 'caption' => 'Hadrah dan sholawat santri'],
            ['image' => 'img_20260724_202636', 'caption' => 'Kajian belajar di kelas'],
            ['image' => 'img_20260725_203052', 'caption' => 'Kajian kitab bersama ulama'],
            ['image' => 'img_20260725_203120', 'caption' => 'Kajian kitab di mushalla'],
            ['image' => 'img_20260808_055517', 'caption' => 'Belajar kitab malam hari'],
            ['image' => 'img_20260809_053909', 'caption' => 'Sorogan bersama asatidz'],
            ['image' => 'img_20260908_074754', 'caption' => 'Perlengkapan kegiatan santri'],
            ['image' => 'img_20260921_185806', 'caption' => 'Sambutan seminar ribath'],
            ['image' => 'img_20260921_190654', 'caption' => 'Seminar bersama dosen'],
            ['image' => 'img_20261014_094433', 'caption' => 'Pembacaan doa acara sekolah'],
            ['image' => 'img_20261019_051420', 'caption' => 'Membaca bersama santri'],
        ];

        foreach ($items as $index => $item) {
            GalleryItem::create([
                'caption' => $item['caption'],
                'image_key' => $item['image'],
                'sort' => $index + 1,
            ]);
        }
    }
}
