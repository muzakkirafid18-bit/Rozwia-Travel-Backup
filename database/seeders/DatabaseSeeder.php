<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // akun super admin
        User::firstOrCreate(['email' => 'administrator@rozwia.com'], [
            'name' => 'Super Administrator ROZWIA',
            'nomor_telepon' => '081111111111',
            'role' => 'administrator',
            'status_akun' => 'active',
            'password' => Hash::make('Administrator123!'),
        ]);

        // akun admin utama
        User::firstOrCreate(['email' => 'admin@rozwia.com'], [
            'name' => 'Administrator Rozwia',
            'nomor_telepon' => '081234567890',
            'role' => 'admin',
            'status_akun' => 'active',
            'password' => Hash::make('Admin123!'),
        ]);

        // akun supervisor
        User::firstOrCreate(['email' => 'supervisor@rozwia.com'], [
            'name' => 'Supervisor Rozwia',
            'nomor_telepon' => '081298765432',
            'role' => 'supervisor',
            'status_akun' => 'active',
            'password' => Hash::make('Supervisor123!'),
        ]);

        // akun jamaah aktif
        User::firstOrCreate(['email' => 'jamaah@rozwia.com'], [
            'name' => 'Gunawan Maryono (Jamaah)',
            'nomor_telepon' => '082321122334',
            'role' => 'jamaah',
            'status_akun' => 'active',
            'password' => Hash::make('Jamaah123!'),
        ]);

        // akun jamaah pending dp
        User::firstOrCreate(['email' => 'pending@rozwia.com'], [
            'name' => 'Calon Jamaah (Pending DP)',
            'nomor_telepon' => '085678901234',
            'role' => 'jamaah',
            'status_akun' => 'inactive',
            'password' => Hash::make('Jamaah123!'),
        ]);

        // data rekening perusahaan
        \App\Models\RekeningPerusahaan::firstOrCreate(['nomor_rekening' => '7123456789'], [
            'nama_bank' => 'Bank Syariah Indonesia (BSI)',
            'atas_nama' => 'PT Pangrango Berkah Digital',
            'nama_perusahaan' => 'PT Pangrango Berkah Digital',
            'alamat_perusahaan' => 'Jl. Pajajaran No. 28, Bogor',
            'status' => 'active',
        ]);

        \App\Models\RekeningPerusahaan::firstOrCreate(['nomor_rekening' => '8823123456'], [
            'nama_bank' => 'BCA',
            'atas_nama' => 'PT Pangrango Berkah Digital',
            'nama_perusahaan' => 'PT Pangrango Berkah Digital',
            'alamat_perusahaan' => 'Jl. Pajajaran No. 28, Bogor',
            'status' => 'active',
        ]);

        // profil perusahaan default
        \App\Models\Perusahaan::firstOrCreate(['nomor_rekening' => '7123456789'], [
            'nama_perusahaan'   => 'Rozwia Islamic Travel',
            'email_perusahaan'  => 'rozwia@gmail.com',
            'telepon_perusahaan'=> '0895-1882-9438',
            'alamat_perusahaan' => 'Graha Kartika Pratama Blok F2 No. 11 Bojong Baru, Bojong Gede, Bogor',
            'nama_bank'         => 'Bank Syariah Indonesia (BSI)',
            'nomor_rekening'    => '7123456789',
            'atas_nama'         => 'PT Rozwia Islamic Travel',
            'no_izin'           => 'PPIU No. 1023/2021',
            'status'            => 'active',
        ]);

        // data maskapai
        foreach (['Garuda Indonesia', 'Saudia Airlines', 'Qatar Airways', 'Emirates'] as $airline) {
            \App\Models\Maskapai::firstOrCreate(['nama_maskapai' => $airline]);
        }

        // data jenis layanan
        foreach ([
            'Umroh Reguler',
            'Umroh VIP',
            'Umroh Plus',
            'Haji Reguler',
            'Haji Khusus / Plus',
            'Haji Furoda',
        ] as $layanan) {
            \App\Models\JenisLayanan::firstOrCreate(['nama_pelayanan' => $layanan]);
        }

        // data tipe kamar
        \App\Models\TipeKamar::firstOrCreate(['nama' => 'Quad'], ['kapasitas' => 4]);
        \App\Models\TipeKamar::firstOrCreate(['nama' => 'Triple'], ['kapasitas' => 3]);
        \App\Models\TipeKamar::firstOrCreate(['nama' => 'Double'], ['kapasitas' => 2]);

        // data hotel
        $hotelMakkah = \App\Models\Hotel::firstOrCreate(['nama_hotel' => 'Pullman Zamzam Makkah'], [
            'bintang_hotel' => 5,
            'lokasi' => 'Makkah',
            'alamat_hotel' => 'Abraj Al Bait Complex, Makkah',
            'foto_hotel' => 'images/sheraton.jpg',
        ]);
        $hotelMadinah = \App\Models\Hotel::firstOrCreate(['nama_hotel' => 'Anwar Al Madinah Mövenpick'], [
            'bintang_hotel' => 5,
            'lokasi' => 'Madinah',
            'alamat_hotel' => 'Central Northern Area, Madinah',
            'foto_hotel' => 'images/crowne.jpg',
        ]);

        // data paket umrah & haji
        $layananUmroh = \App\Models\JenisLayanan::where('nama_pelayanan', 'Umroh VIP')->first();
        $maskapaiSaudia = \App\Models\Maskapai::where('nama_maskapai', 'Saudia Airlines')->first();
        $tipeQuad = \App\Models\TipeKamar::where('nama', 'Quad')->first();
        $tipeDouble = \App\Models\TipeKamar::where('nama', 'Double')->first();

        $paketDefs = [
            // paket umrah
            [
                'nama' => 'January Umroh & Istanbul',
                'kategori' => 'Umroh',
                'layanan' => 'Umroh Plus',
                'harga' => 34500000,
                'durasi' => 9,
                'deskripsi' => 'Paket umroh eksklusif digabungkan dengan wisata religi ke Istanbul, Turki. Fasilitas hotel bintang 5 tepat di pelataran Masjidil Haram.',
                'gambar' => 'images/pilihan paket.jpg',
            ],
            [
                'nama' => 'Sacred Journey',
                'kategori' => 'Umroh',
                'layanan' => 'Umroh Reguler',
                'harga' => 30000000,
                'durasi' => 9,
                'deskripsi' => 'Perjalanan Umroh yang nyaman dan terencana untuk meraih keberkahan di Tanah Suci bersama bimbingan muthawif bersertifikasi.',
                'gambar' => 'images/pilihan paket.jpg',
            ],
            [
                'nama' => 'Serenity Journey',
                'kategori' => 'Umroh',
                'layanan' => 'Umroh VIP',
                'harga' => 35000000,
                'durasi' => 12,
                'deskripsi' => 'Luangkan lebih banyak waktu untuk beribadah dan menikmati ketenangan di Makkah serta Madinah.',
                'gambar' => 'images/destinasi.jpg',
            ],
            [
                'nama' => 'Graceful Journey',
                'kategori' => 'Umroh',
                'layanan' => 'Umroh Reguler',
                'harga' => 40000000,
                'durasi' => 12,
                'deskripsi' => 'Perjalanan Umroh dengan waktu lebih leluasa untuk memperdalam pengalaman spiritual.',
                'gambar' => 'images/destinasi.jpg',
            ],
            [
                'nama' => 'Spiritual Journey',
                'kategori' => 'Umroh',
                'layanan' => 'Umroh Reguler',
                'harga' => 45000000,
                'durasi' => 15,
                'deskripsi' => 'Perjalanan ibadah yang lebih panjang untuk menikmati setiap momen berharga di Tanah Suci.',
                'gambar' => 'images/haji.jpg',
            ],
            [
                'nama' => 'Barakah Journey',
                'kategori' => 'Umroh',
                'layanan' => 'Umroh Reguler',
                'harga' => 48000000,
                'durasi' => 15,
                'deskripsi' => 'Rasakan perjalanan penuh keberkahan dengan waktu ibadah yang lebih leluasa.',
                'gambar' => 'images/haji.jpg',
            ],
            [
                'nama' => 'Faith & Discovery',
                'kategori' => 'Umroh',
                'layanan' => 'Umroh Plus',
                'harga' => 50000000,
                'durasi' => 12,
                'deskripsi' => 'Perjalanan ibadah Umroh yang dilengkapi wisata ke Turki. Kunjungi keindahan Istanbul dan Hagia Sophia.',
                'gambar' => 'images/destinasi.jpg',
            ],
            [
                'nama' => 'Explore & Worship',
                'kategori' => 'Umroh',
                'layanan' => 'Umroh Plus',
                'harga' => 56000000,
                'durasi' => 12,
                'deskripsi' => 'Padukan perjalanan ibadah di Tanah Suci dengan pengalaman menjelajahi Dubai dan gurun pasir.',
                'gambar' => 'images/Masjid nabawi.jpg',
            ],

            // paket haji
            [
                'nama' => 'Perjalanan Haji Penuh Makna',
                'kategori' => 'Haji',
                'layanan' => 'Haji Reguler',
                'harga' => 95000000,
                'durasi' => 40,
                'deskripsi' => 'Wujudkan niat suci menuju Baitullah melalui penyelenggaraan Haji Reguler dengan rangkaian ibadah yang terencana sesuai ketentuan pemerintah.',
                'gambar' => 'images/haji reguler.jpg',
            ],
            [
                'nama' => 'Mabrur Hajj Journey',
                'kategori' => 'Haji',
                'layanan' => 'Haji Khusus / Plus',
                'harga' => 255000000,
                'durasi' => 25,
                'deskripsi' => 'Nikmati perjalanan ibadah Haji dengan layanan khusus dan pendampingan yang dirancang untuk memberikan kenyamanan selama berada di Tanah Suci.',
                'gambar' => 'images/haji mabrur.jpg',
            ],
            [
                'nama' => 'Blessed Hajj Experience',
                'kategori' => 'Haji',
                'layanan' => 'Haji Khusus / Plus',
                'harga' => 310000000,
                'durasi' => 25,
                'deskripsi' => 'Perjalanan istimewa untuk menunaikan ibadah Haji dengan fasilitas dan pelayanan sesuai pilihan program, agar setiap rangkaian ibadah terasa lebih nyaman.',
                'gambar' => 'images/haji plus.jpg',
            ],
            [
                'nama' => 'Exclusive Hajj Journey',
                'kategori' => 'Haji',
                'layanan' => 'Haji Furoda',
                'harga' => 368000000,
                'durasi' => 18,
                'deskripsi' => 'Perjalanan Haji melalui jalur visa undangan dari Pemerintah Arab Saudi, dengan layanan perjalanan yang disesuaikan berdasarkan program penyelenggara.',
                'gambar' => 'images/haji mabrur.jpg',
            ],
            [
                'nama' => 'Royal Hajj Journey',
                'kategori' => 'Haji',
                'layanan' => 'Haji Furoda',
                'harga' => 450000000,
                'durasi' => 30,
                'deskripsi' => 'Rasakan perjalanan ibadah yang berkesan dengan pendampingan dan layanan sesuai program, untuk menemani langkah menuju Baitullah.',
                'gambar' => 'images/furoda.jpg',
            ],
        ];

        $paket1 = null;
        foreach ($paketDefs as $p) {
            $jl = \App\Models\JenisLayanan::where('nama_pelayanan', $p['layanan'])->first() ?? $layananUmroh;
            $paket = \App\Models\Paket::firstOrCreate(['nama_paket' => $p['nama']], [
                'id_jenis_layanan' => $jl ? $jl->id : 1,
                'kategori'         => $p['kategori'],
                'harga_dasar'      => $p['harga'],
                'deskripsi'        => $p['deskripsi'],
                'status'           => 'active',
                'gambar_utama'     => $p['gambar'],
            ]);

            if ($p['nama'] === 'January Umroh & Istanbul') {
                $paket1 = $paket;
            }

            // Buat jadwal jika belum ada
            if (!$paket->jadwals()->exists() && $maskapaiSaudia) {
                \App\Models\Jadwal::create([
                    'id_paket'              => $paket->id,
                    'id_maskapai'           => $maskapaiSaudia->id,
                    'nomor_penerbangan'     => 'SV-819',
                    'tanggal_keberangkatan' => now()->addDays(45),
                    'tanggal_pulang'        => now()->addDays(45 + $p['durasi']),
                    'kuota'                 => 45,
                    'status'                => 'Tersedia',
                ]);
            }

            // Tipe kamar jika belum ada
            if (!$paket->paketTipeKamars()->exists()) {
                if ($tipeQuad) {
                    \App\Models\PaketTipeKamar::create([
                        'id_paket' => $paket->id,
                        'id_hotel' => $hotelMakkah->id,
                        'id_tipe'  => $tipeQuad->id,
                        'harga'    => $p['harga'],
                    ]);
                }
                if ($tipeDouble) {
                    \App\Models\PaketTipeKamar::create([
                        'id_paket' => $paket->id,
                        'id_hotel' => $hotelMakkah->id,
                        'id_tipe'  => $tipeDouble->id,
                        'harga'    => (int) round($p['harga'] * 1.15),
                    ]);
                }
            }
        }

        // jadwal awal paket 1
        if ($maskapaiSaudia) {
            \App\Models\Jadwal::firstOrCreate(['id_paket' => $paket1->id, 'nomor_penerbangan' => 'SV 817'], [
                'id_maskapai' => $maskapaiSaudia->id,
                'tanggal_keberangkatan' => '2027-01-20',
                'tanggal_pulang' => '2027-01-29',
                'kuota' => 45,
                'status' => 'Tersedia',
            ]);
        }

        // relasi kamar & hotel paket 1
        if ($tipeQuad) {
            \App\Models\PaketTipeKamar::firstOrCreate([
                'id_paket' => $paket1->id,
                'id_hotel' => $hotelMakkah->id,
                'id_tipe' => $tipeQuad->id,
            ], [
                'harga' => 34500000,
            ]);
        }
        if ($tipeDouble) {
            \App\Models\PaketTipeKamar::firstOrCreate([
                'id_paket' => $paket1->id,
                'id_hotel' => $hotelMakkah->id,
                'id_tipe' => $tipeDouble->id,
            ], [
                'harga' => 38000000,
            ]);
        }

        // itinerary paket 1
        $tahapans = [
            ['urutan' => 1, 'judul' => 'Keberangkatan dari Indonesia', 'desc' => 'Jamaah berkumpul di Bandara Soekarno-Hatta (CGK) untuk proses briefing, penyerahan paspor & tiket, lalu terbang langsung menuju Bandara Madinah/Jeddah.'],
            ['urutan' => 2, 'judul' => 'Tiba di Madinah & Ziarah Raudhah', 'desc' => 'Tiba di Tanah Suci, check-in hotel bintang 5 dekat pelataran Masjid Nabawi, ziarah ke Makam Rasulullah SAW dan memperbanyak ibadah di Raudhah.'],
            ['urutan' => 3, 'judul' => 'Mengambil Miqat di Bir Ali & Ihram', 'desc' => 'Jamaah mandi sunnah ihram, mengenakan pakaian ihram, berniat umroh di Masjid Bir Ali (Dzulhulaifah), dan bertolak menuju Makkah Al-Mukarramah.'],
            ['urutan' => 4, 'judul' => 'Pelaksanaan Ibadah Umroh di Makkah', 'desc' => 'Melaksanakan rukun umroh dengan bimbingan muthawif: Thawaf mengelilingi Ka’bah 7 putaran, Sa’i antara Shafa dan Marwah, diakhiri Tahallul (cukur rambut).'],
            ['urutan' => 5, 'judul' => 'Wisata Religi & Sejarah di Istanbul', 'desc' => 'Melanjutkan perjalanan spiritual ke Istanbul, Turki. Mengunjungi Hagia Sophia, Blue Mosque, Topkapi Palace, dan menikmati Bosphorus Cruise.'],
            ['urutan' => 6, 'judul' => 'Kepulangan ke Tanah Air', 'desc' => 'Penerbangan kembali ke Indonesia membawa kenangan ibadah yang mabrur dan penuh berkah bersama keluarga.'],
        ];

        foreach ($tahapans as $t) {
            \App\Models\RangkaianIbadah::firstOrCreate([
                'id_paket' => $paket1->id,
                'urutan' => $t['urutan'],
            ], [
                'judul_tahapan' => $t['judul'],
                'deskripsi' => $t['desc'],
                'path_icon' => 'plane',
            ]);
        }
    }
}
