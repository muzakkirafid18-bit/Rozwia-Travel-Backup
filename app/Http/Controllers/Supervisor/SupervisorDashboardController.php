<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Jadwal;
use App\Models\JamaahDetail;
use App\Models\JenisLayanan;
use App\Models\Maskapai;
use App\Models\Paket;
use App\Models\PaketTipeKamar;
use App\Models\Perusahaan;
use App\Models\RiwayatPembayaran;
use App\Models\TipeKamar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupervisorDashboardController extends Controller
{
    /**
     * File penyimpanan identitas perusahaan jika tidak ada tabel terpisah.
     */
    protected function getProfileFilePath(): string
    {
        return \App\Helpers\RozwiaHelper::companyProfilePath();
    }

    protected function getCompanyProfile(): array
    {
        $default = [
            'nama_perusahaan' => 'Rozwia Islamic Travel',
            'slogan'          => 'Penyelenggara Umroh dan Haji Resmi',
            'no_izin'         => 'PPIU No. 1023/2021',
            'email'           => 'Rozwia@gmail.com',
            'telepon'         => '0895-1882-9438',
            'alamat'          => 'Kantor Pusat: Graha Kartika Pratama Blok F2 Nomor 11 Kelurahan Bojong Baru Kecamatan Bojong Gede Kabupaten Bogor Jawa Barat',
            'website'         => 'https://rozwia.travel',
        ];

        $path = $this->getProfileFilePath();
        if (File::exists($path)) {
            $data = json_decode(File::get($path), true);
            if (is_array($data)) {
                return array_merge($default, $data);
            }
        }

        return $default;
    }

    // halaman beranda supervisor
    public function dashboard(): View
    {
        $totalPaket = Paket::count();
        $paketAktif = Paket::where('status', 'active')->count();
        $paketNonaktif = Paket::where('status', '!=', 'active')->count();

        // total pemasukan yang sudah berhasil
        $totalPendapatan = (float) RiwayatPembayaran::where('status', 'Berhasil')->sum('amount');

        // statistik akun jamaah
        $displayTotalAkun = User::where('role', 'jamaah')->count();
        $displayAkunAktif = User::where('role', 'jamaah')->where('status_akun', 'active')->count();
        $displayAkunNonaktif = User::where('role', 'jamaah')->where('status_akun', '!=', 'active')->count();

        // data pendaftaran per bulan (6 bulan terakhir)
        $startMonth = now()->startOfMonth()->subMonths(5);
        $recentBookings = \App\Models\Booking::with('jadwal.paket.jenisLayanan')
            ->where('created_at', '>=', $startMonth)
            ->get();

        $monthlyStats = collect(range(5, 0))->map(function (int $offset) use ($recentBookings) {
            $month = now()->startOfMonth()->subMonths($offset);
            $monthBookings = $recentBookings->filter(
                fn ($b) => $b->created_at && $b->created_at->format('Y-m') === $month->format('Y-m')
            );

            $umrohCount = 0;
            $hajiCount = 0;
            foreach ($monthBookings as $b) {
                $paket = $b->jadwal?->paket;
                $label = strtolower(($paket?->kategori ?? '') . ' ' . ($paket?->jenisLayanan?->nama_layanan ?? '') . ' ' . ($paket?->nama_paket ?? ''));
                $qty = max(1, (int) ($b->jumlah_jamaah ?? 1));
                if (str_contains($label, 'haji')) {
                    $hajiCount += $qty;
                } else {
                    $umrohCount += $qty;
                }
            }

            return [
                'bulan' => $month->translatedFormat('M Y'),
                'umroh' => $umrohCount,
                'haji'  => $hajiCount,
            ];
        })->values()->all();

        return view('supervisor.dashboard', compact(
            'totalPaket', 'paketAktif', 'paketNonaktif',
            'totalPendapatan', 'displayTotalAkun', 'displayAkunAktif', 'displayAkunNonaktif',
            'monthlyStats'
        ));
    }

    // halaman profil perusahaan
    public function profile(): View
    {
        $perusahaan = \App\Models\Perusahaan::first();
        $profile = [
            'nama_perusahaan'    => $perusahaan?->nama_perusahaan ?? 'Rozwia Islamic Travel',
            'email_perusahaan'   => $perusahaan?->email_perusahaan ?? 'rozwia@gmail.com',
            'telepon_perusahaan' => $perusahaan?->telepon_perusahaan ?? '0895-1882-9438',
            'alamat_perusahaan'  => $perusahaan?->alamat_perusahaan ?? 'Graha Kartika Pratama Blok F2 No. 11 Bojong Baru, Bojong Gede, Bogor',
            'nama_bank'          => $perusahaan?->nama_bank ?? 'Bank Syariah Indonesia',
            'nomor_rekening'     => $perusahaan?->nomor_rekening ?? '7123456789',
            'atas_nama'          => $perusahaan?->atas_nama ?? 'PT Rozwia Islamic Travel',
            'no_izin'            => $perusahaan?->no_izin ?? 'PPIU No. 1023/2021',
        ];
        $rekenings = collect([$perusahaan])->filter();
        return view('supervisor.profile', compact('profile', 'rekenings', 'perusahaan'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_perusahaan'    => ['required', 'string', 'max:150'],
            'email_perusahaan'   => ['required', 'email', 'max:100'],
            'telepon_perusahaan' => ['required', 'string', 'max:50'],
            'alamat_perusahaan'  => ['required', 'string', 'max:500'],
            'nama_bank'          => ['required', 'string', 'max:100'],
            'nomor_rekening'     => ['required', 'string', 'max:50'],
            'atas_nama'          => ['required', 'string', 'max:100'],
            'no_izin'            => ['nullable', 'string', 'max:100'],
        ]);

        \App\Models\Perusahaan::updateOrCreate([], $validated);

        return back()->with('success', 'Profile & Identitas Perusahaan berhasil disimpan!');
    }

    // halaman kelola paket
    public function paket(): View
    {
        $pakets = Paket::with([
                'jenisLayanan',
                'jadwals.maskapai',
                'jadwals.bookings',
                'paketTipeKamars.hotel',
                'paketTipeKamars.tipeKamar',
            ])
            ->latest()
            ->get();

        $jenisLayanans = JenisLayanan::all();
        $maskapais     = Maskapai::all();
        $hotels        = Hotel::all();
        $tipeKamars    = TipeKamar::all();

        return view('supervisor.paket', compact('pakets', 'jenisLayanans', 'maskapais', 'hotels', 'tipeKamars'));
    }

    /**
     * Simpan gambar paket secara aman ke public/images/pakets dengan nama acak.
     */
    private function savePaketImage(?UploadedFile $file, string $kategori, ?string $oldPath = null): string
    {
        if (!$file) {
            return $oldPath ?: (strtolower($kategori) === 'haji' ? 'images/paket haji.jpg' : 'images/pilihan paket.jpg');
        }

        $targetDir = public_path('images/pakets');
        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $ext = 'jpg';
        }

        $filename = Str::random(32) . '.' . $ext;
        $file->move($targetDir, $filename);

        $this->deleteUploadedPaketImage($oldPath);

        return 'images/pakets/' . $filename;
    }

    private function deleteUploadedPaketImage(?string $path): void
    {
        if ($path && str_starts_with($path, 'images/pakets/')) {
            $full = public_path($path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }
    }

    public function storePaket(Request $request): RedirectResponse
    {
        foreach (['harga', 'harga_quad', 'harga_triple', 'harga_double', 'harga_madinah_quad', 'harga_madinah_triple', 'harga_madinah_double', 'harga_makkah_quad', 'harga_makkah_triple', 'harga_makkah_double'] as $priceKey) {
            if ($request->filled($priceKey) && is_string($request->input($priceKey))) {
                $clean = preg_replace('/[^\d]/', '', $request->input($priceKey));
                $request->merge([$priceKey => $clean === '' ? null : $clean]);
            }
        }

        $validated = $request->validate([
            'nama_paket'        => ['required', 'string', 'min:3', 'max:100'],
            'kategori'          => ['required', 'in:Umrah,Haji'],
            'id_jenis_layanan'  => ['nullable', 'exists:jenis_layanan,id'],
            'harga'             => ['nullable', 'numeric', 'min:100000', 'required_without_all:harga_quad,harga_makkah_quad,harga_madinah_quad'],
            'durasi'            => ['nullable', 'integer', 'min:1', 'max:90'],
            'deskripsi'         => ['nullable', 'string', 'max:5000'],
            'fasilitas'         => ['nullable', 'array'],
            'fasilitas.*'       => ['string', 'max:100'],
            'status'            => ['required', 'string', 'max:20'],
            'gambar_utama'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            // data jadwal & maskapai
            'tanggal_mulai'     => ['nullable', 'date'],
            'tanggal_pulang'    => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'id_maskapai'       => ['nullable', 'exists:maskapai,id'],
            'nomor_penerbangan' => ['nullable', 'string', 'max:50'],
            'tempat_kumpul'     => ['nullable', 'string', 'max:200'],
            'tanggal_kumpul'    => ['nullable', 'date'],
            'kuota'             => ['nullable', 'integer', 'min:1', 'max:500'],
            // data hotel & harga per tipe kamar
            'id_hotel_makkah'      => ['nullable', 'exists:hotel,id'],
            'id_hotel_madinah'     => ['nullable', 'exists:hotel,id'],
            'harga_quad'           => ['nullable', 'numeric', 'min:100000'],
            'harga_triple'         => ['nullable', 'numeric', 'min:0'],
            'harga_double'         => ['nullable', 'numeric', 'min:0'],
            'harga_madinah_quad'   => ['nullable', 'numeric', 'min:0'],
            'harga_madinah_triple' => ['nullable', 'numeric', 'min:0'],
            'harga_madinah_double' => ['nullable', 'numeric', 'min:0'],
            'harga_makkah_quad'    => ['nullable', 'numeric', 'min:0'],
            'harga_makkah_triple'  => ['nullable', 'numeric', 'min:0'],
            'harga_makkah_double'  => ['nullable', 'numeric', 'min:0'],
        ]);

        $hargaDasar = (float) ($validated['harga_quad']
            ?? $validated['harga_makkah_quad']
            ?? $validated['harga_madinah_quad']
            ?? $validated['harga']);
        $validated['harga'] = $hargaDasar;

        $idJenisLayanan = $validated['id_jenis_layanan'] ?? (JenisLayanan::first()?->id ?? 1);
        $imagePath = $this->savePaketImage($request->file('gambar_utama'), $validated['kategori']);
        $deskripsi = !empty(trim($validated['deskripsi'] ?? ''))
            ? trim($validated['deskripsi'])
            : 'Perjalanan ibadah aman, nyaman, dan terpercaya bersama bimbingan muthawif bersertifikasi ROZWIA.';
        $fasilitas = array_values(array_unique(array_filter(array_map('trim', $validated['fasilitas'] ?? []))));

        $paket = DB::transaction(function () use ($validated, $idJenisLayanan, $imagePath, $deskripsi, $fasilitas) {
            $paket = Paket::create([
                'id_jenis_layanan' => $idJenisLayanan,
                'nama_paket'       => $validated['nama_paket'],
                'kategori'         => $validated['kategori'],
                'harga_dasar'      => $validated['harga'],
                'deskripsi'        => $deskripsi,
                'fasilitas'        => !empty($fasilitas) ? $fasilitas : null,
                'status'           => in_array(strtolower($validated['status']), ['nonaktif', 'inactive', 'draft'], true) ? 'inactive' : 'active',
                'gambar_utama'     => $imagePath,
            ]);

            if (!empty($validated['tanggal_mulai'])) {
                $tglMulai = Carbon::parse($validated['tanggal_mulai']);
                $durasi = (int) ($validated['durasi'] ?? 9);
                $tglPulang = !empty($validated['tanggal_pulang'])
                    ? Carbon::parse($validated['tanggal_pulang'])
                    : $tglMulai->copy()->addDays($durasi);

                $idMaskapai = $validated['id_maskapai'] ?? (Maskapai::first()?->id ?? 1);
                $kuota = (int) ($validated['kuota'] ?? 40);

                Jadwal::create([
                    'id_paket'              => $paket->id,
                    'id_maskapai'           => $idMaskapai,
                    'nomor_penerbangan'     => $validated['nomor_penerbangan'] ?? null,
                    'tempat_kumpul'         => $validated['tempat_kumpul'] ?? null,
                    'tanggal_kumpul'        => !empty($validated['tanggal_kumpul']) ? Carbon::parse($validated['tanggal_kumpul']) : null,
                    'tanggal_keberangkatan' => $tglMulai,
                    'tanggal_pulang'        => $tglPulang,
                    'kuota'                 => $kuota,
                    'status'                => 'Tersedia',
                ]);
            }

            $this->syncPaketHotels($paket, $validated);

            return $paket;
        });

        return redirect()->route('supervisor.paket')->with('success', "Alhamdulillah! Paket '{$paket->nama_paket}' berhasil ditambahkan.");
    }

    public function updatePaket(Request $request, int $id): RedirectResponse
    {
        $paket = Paket::with(['jadwals', 'paketTipeKamars'])->findOrFail($id);

        foreach (['harga', 'harga_quad', 'harga_triple', 'harga_double', 'harga_madinah_quad', 'harga_madinah_triple', 'harga_madinah_double', 'harga_makkah_quad', 'harga_makkah_triple', 'harga_makkah_double'] as $priceKey) {
            if ($request->filled($priceKey) && is_string($request->input($priceKey))) {
                $clean = preg_replace('/[^\d]/', '', $request->input($priceKey));
                $request->merge([$priceKey => $clean === '' ? null : $clean]);
            }
        }

        $validated = $request->validate([
            'nama_paket'        => ['required', 'string', 'min:3', 'max:100'],
            'kategori'          => ['required', 'in:Umrah,Haji'],
            'id_jenis_layanan'  => ['nullable', 'exists:jenis_layanan,id'],
            'harga'             => ['nullable', 'numeric', 'min:100000', 'required_without_all:harga_quad,harga_makkah_quad,harga_madinah_quad'],
            'durasi'            => ['nullable', 'integer', 'min:1', 'max:90'],
            'deskripsi'         => ['nullable', 'string', 'max:5000'],
            'fasilitas'         => ['nullable', 'array'],
            'fasilitas.*'       => ['string', 'max:100'],
            'status'            => ['required', 'string', 'max:20'],
            'gambar_utama'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'hapus_gambar'      => ['nullable', 'boolean'],
            'tanggal_mulai'     => ['nullable', 'date'],
            'tanggal_pulang'    => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'id_maskapai'       => ['nullable', 'exists:maskapai,id'],
            'nomor_penerbangan' => ['nullable', 'string', 'max:50'],
            'tempat_kumpul'     => ['nullable', 'string', 'max:200'],
            'tanggal_kumpul'    => ['nullable', 'date'],
            'kuota'             => ['nullable', 'integer', 'min:1', 'max:500'],
            'id_hotel_makkah'      => ['nullable', 'exists:hotel,id'],
            'id_hotel_madinah'     => ['nullable', 'exists:hotel,id'],
            'harga_quad'           => ['nullable', 'numeric', 'min:100000'],
            'harga_triple'         => ['nullable', 'numeric', 'min:0'],
            'harga_double'         => ['nullable', 'numeric', 'min:0'],
            'harga_madinah_quad'   => ['nullable', 'numeric', 'min:0'],
            'harga_madinah_triple' => ['nullable', 'numeric', 'min:0'],
            'harga_madinah_double' => ['nullable', 'numeric', 'min:0'],
            'harga_makkah_quad'    => ['nullable', 'numeric', 'min:0'],
            'harga_makkah_triple'  => ['nullable', 'numeric', 'min:0'],
            'harga_makkah_double'  => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['harga'] = (float) ($validated['harga_quad']
            ?? $validated['harga_makkah_quad']
            ?? $validated['harga_madinah_quad']
            ?? $validated['harga']
            ?? $paket->harga_dasar);

        $imagePath = $paket->gambar_utama;
        if ($request->boolean('hapus_gambar')) {
            $this->deleteUploadedPaketImage($paket->gambar_utama);
            $imagePath = strtolower($validated['kategori']) === 'haji' ? 'images/paket haji.jpg' : 'images/pilihan paket.jpg';
        }
        if ($request->hasFile('gambar_utama')) {
            $imagePath = $this->savePaketImage($request->file('gambar_utama'), $validated['kategori'], $paket->gambar_utama);
        }

        $deskripsi = !empty(trim($validated['deskripsi'] ?? ''))
            ? trim($validated['deskripsi'])
            : ($paket->deskripsi ?: 'Perjalanan ibadah aman, nyaman, dan terpercaya bersama bimbingan muthawif bersertifikasi ROZWIA.');
        $fasilitas = array_key_exists('fasilitas', $validated)
            ? array_values(array_unique(array_filter(array_map('trim', $validated['fasilitas'] ?? []))))
            : $paket->fasilitas;

        DB::transaction(function () use ($paket, $validated, $imagePath, $deskripsi, $fasilitas) {
            $paket->update([
                'id_jenis_layanan' => $validated['id_jenis_layanan'] ?? $paket->id_jenis_layanan,
                'nama_paket'       => $validated['nama_paket'],
                'kategori'         => $validated['kategori'],
                'harga_dasar'      => $validated['harga'],
                'deskripsi'        => $deskripsi,
                'fasilitas'        => $fasilitas,
                'status'           => in_array(strtolower($validated['status']), ['nonaktif', 'inactive', 'draft'], true) ? 'inactive' : 'active',
                'gambar_utama'     => $imagePath,
            ]);

            if (!empty($validated['tanggal_mulai'])) {
                $tglMulai = Carbon::parse($validated['tanggal_mulai']);
                $durasi = (int) ($validated['durasi'] ?? 9);
                $tglPulang = !empty($validated['tanggal_pulang'])
                    ? Carbon::parse($validated['tanggal_pulang'])
                    : $tglMulai->copy()->addDays($durasi);

                $firstJadwal = $paket->jadwals()->first();
                $jadwalPayload = [
                    'id_maskapai'           => $validated['id_maskapai'] ?? ($firstJadwal?->id_maskapai ?? Maskapai::first()?->id ?? 1),
                    'nomor_penerbangan'     => $validated['nomor_penerbangan'] ?? $firstJadwal?->nomor_penerbangan,
                    'tempat_kumpul'         => array_key_exists('tempat_kumpul', $validated) ? $validated['tempat_kumpul'] : $firstJadwal?->tempat_kumpul,
                    'tanggal_kumpul'        => !empty($validated['tanggal_kumpul']) ? Carbon::parse($validated['tanggal_kumpul']) : ($firstJadwal?->tanggal_kumpul),
                    'tanggal_keberangkatan' => $tglMulai,
                    'tanggal_pulang'        => $tglPulang,
                    'kuota'                 => (int) ($validated['kuota'] ?? $firstJadwal?->kuota ?? 40),
                ];

                if ($firstJadwal) {
                    $firstJadwal->update($jadwalPayload);
                } else {
                    Jadwal::create(array_merge($jadwalPayload, [
                        'id_paket' => $paket->id,
                        'status'   => 'Tersedia',
                    ]));
                }
            }

            if (
                !empty($validated['id_hotel_makkah']) || !empty($validated['id_hotel_madinah'])
                || !empty($validated['harga_quad']) || !empty($validated['harga_makkah_quad']) || !empty($validated['harga_madinah_quad'])
            ) {
                $paket->paketTipeKamars()->delete();
                $this->syncPaketHotels($paket, $validated);
            }
        });

        return redirect()->route('supervisor.paket')->with('success', "Perubahan pada paket '{$paket->nama_paket}' berhasil disimpan.");
    }

    private function syncPaketHotels(Paket $paket, array $validated): void
    {
        $makkahId = $validated['id_hotel_makkah'] ?? Hotel::where('lokasi', 'Makkah')->value('id');
        $madinahId = $validated['id_hotel_madinah'] ?? Hotel::where('lokasi', 'Madinah')->value('id');

        $hotelsWithPrices = [];
        if ($madinahId) {
            $hotelsWithPrices[$madinahId] = [
                'Quad'   => $validated['harga_madinah_quad'] ?? $validated['harga_quad'] ?? $validated['harga'],
                'Triple' => $validated['harga_madinah_triple'] ?? $validated['harga_triple'] ?? ($validated['harga'] + 3000000),
                'Double' => $validated['harga_madinah_double'] ?? $validated['harga_double'] ?? ($validated['harga'] + 7000000),
            ];
        }
        if ($makkahId) {
            $hotelsWithPrices[$makkahId] = [
                'Quad'   => $validated['harga_makkah_quad'] ?? $validated['harga_quad'] ?? $validated['harga'],
                'Triple' => $validated['harga_makkah_triple'] ?? $validated['harga_triple'] ?? ($validated['harga'] + 3000000),
                'Double' => $validated['harga_makkah_double'] ?? $validated['harga_double'] ?? ($validated['harga'] + 7000000),
            ];
        }

        if (empty($hotelsWithPrices)) {
            $firstHotel = Hotel::first();
            if ($firstHotel) {
                $hotelsWithPrices[$firstHotel->id] = [
                    'Quad'   => $validated['harga_quad'] ?? $validated['harga'],
                    'Triple' => $validated['harga_triple'] ?? ($validated['harga'] + 3000000),
                    'Double' => $validated['harga_double'] ?? ($validated['harga'] + 7000000),
                ];
            }
        }

        foreach ($hotelsWithPrices as $hotelId => $tipeKamarMap) {
            foreach ($tipeKamarMap as $tipeNama => $hargaKamar) {
                $tipe = TipeKamar::firstOrCreate(['nama' => $tipeNama], ['kapasitas' => match ($tipeNama) {
                    'Double' => 2,
                    'Triple' => 3,
                    default  => 4,
                }]);

                PaketTipeKamar::create([
                    'id_paket' => $paket->id,
                    'id_hotel' => $hotelId,
                    'id_tipe'  => $tipe->id,
                    'harga'    => $hargaKamar,
                ]);
            }
        }
    }

    public function togglePaketStatus(int $id): RedirectResponse
    {
        $paket = Paket::findOrFail($id);
        $statusBaru = $paket->status === 'active' ? 'inactive' : 'active';
        $paket->update(['status' => $statusBaru]);

        return back()->with('success', "Status paket {$paket->nama_paket} berhasil diubah menjadi " . ($statusBaru === 'active' ? 'Aktif' : 'Nonaktif') . ".");
    }

    public function deletePaket(int $id): RedirectResponse
    {
        $paket = Paket::with(['jadwals.bookings', 'paketTipeKamars', 'gambarPakets', 'rangkaianIbadahs'])->findOrFail($id);
        $nama = $paket->nama_paket;

        $hasBookings = $paket->jadwals->flatMap->bookings->isNotEmpty();
        if ($hasBookings) {
            $paket->update(['status' => 'inactive']);
            return back()->with('success', "Paket {$nama} memiliki riwayat booking jamaah sehingga dinonaktifkan.");
        }

        $this->deleteUploadedPaketImage($paket->gambar_utama);
        $paket->paketTipeKamars()->delete();
        $paket->gambarPakets()->delete();
        $paket->rangkaianIbadahs()->delete();
        $paket->jadwals()->delete();
        $paket->delete();

        return back()->with('success', "Paket {$nama} berhasil dihapus.");
    }

    // halaman daftar akun jamaah
    public function users(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = User::where('role', 'jamaah')->with(['bookings.jadwal.paket'])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nomor_telepon', 'like', "%{$search}%");
            });
        }

        if ($status && $status !== 'semua') {
            $query->where('status_akun', $status);
        }

        $users = $query->paginate(15)->withQueryString();

        $totalAkun    = User::where('role', 'jamaah')->count();
        $akunAktif    = User::where('role', 'jamaah')->where('status_akun', 'active')->count();
        $akunNonaktif = User::where('role', 'jamaah')->where('status_akun', '!=', 'active')->count();

        return view('supervisor.users', compact('users', 'totalAkun', 'akunAktif', 'akunNonaktif', 'search', 'status'));
    }

    private function sanitizeCsvValue(mixed $value): string
    {
        $str = (string) ($value ?? '');
        if ($str !== '' && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $str;
        }
        return $str;
    }

    public function exportUsers(Request $request): StreamedResponse
    {
        $query = User::where('role', 'jamaah')->with(['bookings.jadwal.paket']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (($status = $request->input('status')) && in_array($status, ['active', 'inactive'], true)) {
            $query->where('status_akun', $status);
        }

        $users = $query->latest()->get();

        $filename = 'daftar_akun_jamaah_rozwia_' . date('Y-m-d') . '.xls';

        $headers = [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        $callback = function () use ($users) {
            echo "\xEF\xBB\xBF";
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8"><style>';
            echo 'table { border-collapse: collapse; font-family: Calibri, Arial, sans-serif; font-size: 11pt; }';
            echo 'th { background-color: #063B25; color: #FFFFFF; font-weight: bold; border: 1px solid #333333; padding: 8px 12px; text-align: left; }';
            echo 'td { border: 1px solid #BFBFBF; padding: 6px 12px; vertical-align: middle; }';
            echo '</style></head><body><table>';
            echo '<thead><tr>'
                . '<th style="width:60px;">ID</th>'
                . '<th style="width:200px;">Nama Pengguna</th>'
                . '<th style="width:240px;">Email</th>'
                . '<th style="width:150px;">No Telepon</th>'
                . '<th style="width:240px;">Paket</th>'
                . '<th style="width:150px;">Tgl Registrasi</th>'
                . '<th style="width:110px;">Status</th>'
                . '</tr></thead><tbody>';

            foreach ($users as $u) {
                $paketNama = $u->bookings->first()?->jadwal?->paket?->nama_paket ?? '-';
                $statusLabel = $u->status_akun === 'active' ? 'Aktif' : 'Nonaktif';
                echo '<tr>'
                    . '<td>' . e((string) $u->id) . '</td>'
                    . '<td>' . e($this->sanitizeCsvValue($u->name)) . '</td>'
                    . '<td>' . e($this->sanitizeCsvValue($u->email)) . '</td>'
                    . '<td style="mso-number-format:\'@\';">' . e($this->sanitizeCsvValue($u->nomor_telepon ?: '-')) . '</td>'
                    . '<td>' . e($this->sanitizeCsvValue($paketNama)) . '</td>'
                    . '<td>' . e($u->created_at?->format('d/m/Y') ?? '-') . '</td>'
                    . '<td>' . e($statusLabel) . '</td>'
                    . '</tr>';
            }

            echo '</tbody></table></body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }

    // halaman laporan total biaya
    private function buildBiayaReport(Request $request): array
    {
        $tglMulai = $request->input('tanggal_mulai');
        $tglAkhir = $request->input('tanggal_akhir');
        $search   = $request->input('search');

        $startDate = $tglMulai ? Carbon::parse($tglMulai)->startOfDay() : null;
        $endDate   = $tglAkhir ? Carbon::parse($tglAkhir)->endOfDay() : null;

        $paketsQuery = Paket::with(['jadwals.bookings.jamaahDetails', 'jadwals.bookings.invoices.riwayatPembayarans']);

        if ($search) {
            $paketsQuery->where('nama_paket', 'like', "%{$search}%");
        }

        $pakets = $paketsQuery->get()->map(function ($paket) use ($startDate, $endDate) {
            $bookings = $paket->jadwals->flatMap->bookings;

            if ($startDate || $endDate) {
                $bookings = $bookings->filter(function ($b) use ($startDate, $endDate) {
                    if (!$b->created_at) {
                        return false;
                    }
                    if ($startDate && $b->created_at->lt($startDate)) {
                        return false;
                    }
                    if ($endDate && $b->created_at->gt($endDate)) {
                        return false;
                    }
                    return true;
                });
            }

            $totalJamaah = (int) ($bookings->sum('jumlah_jamaah') ?: $bookings->flatMap->jamaahDetails->count());

            $payments = $bookings->flatMap->invoices->flatMap->riwayatPembayarans->where('status', 'Berhasil');
            if ($startDate || $endDate) {
                $payments = $payments->filter(function ($p) use ($startDate, $endDate) {
                    $ts = $p->verified_at ?? $p->created_at;
                    if (!$ts) {
                        return false;
                    }
                    if ($startDate && $ts->lt($startDate)) {
                        return false;
                    }
                    if ($endDate && $ts->gt($endDate)) {
                        return false;
                    }
                    return true;
                });
            }
            $totalBayar = (float) $payments->sum('amount');

            return [
                'nama'   => $paket->nama_paket,
                'jumlah' => $totalJamaah,
                'total'  => $totalBayar,
            ];
        });

        return [
            'pakets'         => $pakets,
            'totalJamaahSum' => (int) $pakets->sum('jumlah'),
            'totalBayarSum'  => (float) $pakets->sum('total'),
            'tglMulai'       => $tglMulai ?: now()->startOfYear()->format('Y-m-d'),
            'tglAkhir'       => $tglAkhir ?: now()->endOfYear()->format('Y-m-d'),
            'hasDateFilter'  => (bool) ($tglMulai || $tglAkhir),
            'rawTglMulai'    => $tglMulai,
            'rawTglAkhir'    => $tglAkhir,
            'search'         => $search,
        ];
    }

    public function biaya(Request $request): View
    {
        $report = $this->buildBiayaReport($request);

        return view('supervisor.biaya', $report);
    }

    public function exportBiaya(Request $request): StreamedResponse
    {
        $report = $this->buildBiayaReport($request);
        $pakets = $report['pakets'];

        $filename = 'laporan_keuangan_paket_rozwia_' . date('Y-m-d') . '.xls';

        $headers = [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        $callback = function () use ($pakets) {
            echo "\xEF\xBB\xBF";
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8"><style>';
            echo 'table { border-collapse: collapse; font-family: Calibri, Arial, sans-serif; font-size: 11pt; }';
            echo 'th { background-color: #063B25; color: #FFFFFF; font-weight: bold; border: 1px solid #333333; padding: 8px 14px; }';
            echo 'td { border: 1px solid #BFBFBF; padding: 6px 14px; vertical-align: middle; }';
            echo '.total-row td { background-color: #EFE7D6; font-weight: bold; border-top: 2px solid #063B25; }';
            echo '</style></head><body><table>';
            echo '<thead><tr>'
                . '<th style="width:280px; text-align:left;">Paket</th>'
                . '<th style="width:140px; text-align:center;">Jumlah Jamaah</th>'
                . '<th style="width:200px; text-align:right;">Total Bayar (Rp)</th>'
                . '</tr></thead><tbody>';

            foreach ($pakets as $p) {
                echo '<tr>'
                    . '<td>' . e($this->sanitizeCsvValue($p['nama'])) . '</td>'
                    . '<td style="text-align:center;">' . (int) $p['jumlah'] . '</td>'
                    . '<td style="text-align:right;">Rp ' . number_format((float) $p['total'], 0, ',', '.') . '</td>'
                    . '</tr>';
            }

            echo '<tr class="total-row">'
                . '<td>TOTAL KESELURUHAN</td>'
                . '<td style="text-align:center;">' . (int) $pakets->sum('jumlah') . '</td>'
                . '<td style="text-align:right;">Rp ' . number_format((float) $pakets->sum('total'), 0, ',', '.') . '</td>'
                . '</tr>';
            echo '</tbody></table></body></html>';
        };

        return response()->stream($callback, 200, $headers);
    }
}
