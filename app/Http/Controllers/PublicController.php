<?php

namespace App\Http\Controllers;

use App\Helpers\RozwiaHelper;
use App\Models\Paket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    /**
     * Halaman Utama / Beranda
     */
    public function home(): View
    {
        $pakets = Paket::with(['jenisLayanan', 'jadwals.maskapai', 'paketTipeKamars.hotel'])
            ->where('status', 'active')
            ->latest('id')
            ->get();

        return view('pages.home', compact('pakets'));
    }

    /**
     * Katalog Paket Umroh
     */
    public function umroh(Request $request): View
    {
        $filter = $request->query('filter', 'semua');
        
        $query = Paket::with(['jenisLayanan', 'jadwals.maskapai', 'paketTipeKamars.hotel'])
            ->whereIn('kategori', ['Umroh', 'Umrah'])
            ->where('status', 'active');

        $pakets = $query->latest('id')->get();

        return view('pages.umroh', compact('pakets', 'filter'));
    }

    /**
     * Katalog Paket Haji
     */
    public function haji(Request $request): View
    {
        $filter = $request->query('filter', 'semua');

        $query = Paket::with(['jenisLayanan', 'jadwals.maskapai', 'paketTipeKamars.hotel'])
            ->where('kategori', 'Haji')
            ->where('status', 'active');

        $pakets = $query->latest('id')->get();

        return view('pages.haji', compact('pakets', 'filter'));
    }

    /**
     * Halaman Detail Paket (Umroh & Haji)
     */
    public function detail(int|string $id): View
    {
        $dbPackage = Paket::with([
            'jenisLayanan',
            'jadwals.maskapai',
            'paketTipeKamars.hotel',
            'paketTipeKamars.tipeKamar',
            'rangkaianIbadahs',
            'gambarPakets',
        ])->find($id);

        if (!$dbPackage) {
            // fallback ke paket pertama kalau id nggak ketemu
            $dbPackage = Paket::with([
                'jenisLayanan',
                'jadwals.maskapai',
                'paketTipeKamars.hotel',
                'paketTipeKamars.tipeKamar',
                'rangkaianIbadahs',
                'gambarPakets',
            ])->first();
        }

        // rapikan data paket buat dikirim ke view
        $jadwalPertama = $dbPackage?->jadwals->first();
        $hotelMakkahObj = $dbPackage?->paketTipeKamars->firstWhere('hotel.lokasi', 'Makkah')?->hotel
            ?? $dbPackage?->paketTipeKamars->first()?->hotel;
        $hotelMakkah = $hotelMakkahObj?->nama_hotel ?? 'Fairmont Makkah (Bintang 5)';
        $hotelMakkahImage = $hotelMakkahObj?->foto_hotel
            ? asset($hotelMakkahObj->foto_hotel)
            : asset('images/sheraton.jpg');

        $hotelMadinahObj = $dbPackage?->paketTipeKamars->firstWhere('hotel.lokasi', 'Madinah')?->hotel;
        $hotelMadinah = $hotelMadinahObj?->nama_hotel ?? 'Anwar Al Madinah Mövenpick';
        $hotelMadinahImage = $hotelMadinahObj?->foto_hotel
            ? asset($hotelMadinahObj->foto_hotel)
            : asset('images/crowne.jpg');

        $rawImage = $dbPackage?->gambar_utama;
        $fallbackLocalImage = strtolower((string) ($dbPackage?->kategori ?? '')) === 'haji'
            ? asset('images/paket haji.jpg')
            : (str_contains(strtolower((string) ($dbPackage?->nama_paket ?? '')), 'serenity')
                ? asset('images/Masjid nabawi.jpg')
                : (str_contains(strtolower((string) ($dbPackage?->nama_paket ?? '')), 'sacred')
                    ? asset('images/makkah home.jpg')
                    : asset('images/pilihan paket.jpg')));

        if (empty($rawImage) || str_contains($rawImage, 'images.unsplash.com')) {
            $resolvedImage = $fallbackLocalImage;
        } elseif (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://')) {
            $resolvedImage = $rawImage;
        } else {
            $resolvedImage = asset(ltrim($rawImage, '/'));
        }

        $quadBaseHarga = $dbPackage ? $dbPackage->quadPrice() : 34500000;

        $package = [
            'id' => $dbPackage?->id ?? 1,
            'name' => $dbPackage?->nama_paket ?? 'January Umroh & Istanbul',
            'type' => $dbPackage?->kategori ?? 'Umroh',
            'duration' => $jadwalPertama ? ($jadwalPertama->tanggal_keberangkatan->diffInDays($jadwalPertama->tanggal_pulang) . ' Hari') : '9 Hari',
            'badge' => $dbPackage?->jenisLayanan?->nama_pelayanan ?? 'Reguler',
            'image' => $resolvedImage,
            'description' => $dbPackage?->deskripsi ?? 'Paket umroh eksklusif digabungkan dengan wisata religi bersama ROZWIA.',
            'fasilitas' => (is_array($dbPackage?->fasilitas) && count($dbPackage->fasilitas) > 0)
                ? $dbPackage->fasilitas
                : ['Tiket Pesawat PP', 'Hotel Makkah & Madinah', 'Visa Resmi', 'Manasik', 'Muthawif Berpengalaman', 'Handling & Zamzam 5L'],
            'jadwal' => $jadwalPertama ? ($jadwalPertama->tanggal_keberangkatan->format('d/m/Y') . ' - ' . $jadwalPertama->tanggal_pulang->format('d/m/Y')) : '20/01/2026 - 28/01/2026',
            'airline' => $jadwalPertama?->maskapai?->nama_maskapai ?? 'Saudia Airlines',
            'hotel_makkah' => $hotelMakkah,
            'hotel_makkah_image' => $hotelMakkahImage,
            'hotel_madinah' => $hotelMadinah,
            'hotel_madinah_image' => $hotelMadinahImage,
            'price' => RozwiaHelper::formatRupiah($quadBaseHarga),
        ];

        // ambil daftar harga per tipe kamar
        $kamars = $dbPackage?->paketTipeKamars ?? collect();
        $baseHarga = (float) ($dbPackage?->harga_dasar ?? $quadBaseHarga);

        $resolveKamarPrice = function (string $tipe, float $defaultDelta) use ($kamars, $baseHarga): float {
            $matched = $kamars->filter(fn($k) => strtolower(trim((string) ($k->tipeKamar?->nama ?? ''))) === $tipe);
            if ($matched->isEmpty()) {
                return $baseHarga + $defaultDelta;
            }
            $firstHarga = (float) $matched->first()->harga;
            return $firstHarga >= $baseHarga
                ? $firstHarga
                : ($baseHarga + (float) $matched->sum('harga'));
        };

        $quadPrice = $resolveKamarPrice('quad', 0);
        $triplePrice = $resolveKamarPrice('triple', 3000000);
        $doublePrice = $resolveKamarPrice('double', 7000000);

        $kamarPrices = [
            'quad' => [
                'nama' => 'QUAD (4 ORANG)',
                'label' => 'Quad Room (4 Orang)',
                'raw' => $quadPrice,
                'formatted' => RozwiaHelper::formatRupiah($quadPrice, false),
                'full' => RozwiaHelper::formatRupiah($quadPrice),
            ],
            'triple' => [
                'nama' => 'TRIPLE (3 ORANG)',
                'label' => 'Triple Room (3 Orang)',
                'raw' => $triplePrice,
                'formatted' => RozwiaHelper::formatRupiah($triplePrice, false),
                'full' => RozwiaHelper::formatRupiah($triplePrice),
            ],
            'double' => [
                'nama' => 'DOUBLE (2 ORANG)',
                'label' => 'Double Room (2 Orang)',
                'raw' => $doublePrice,
                'formatted' => RozwiaHelper::formatRupiah($doublePrice, false),
                'full' => RozwiaHelper::formatRupiah($doublePrice),
            ],
        ];

        if (strtolower($package['type']) === 'haji') {
            return view('pages.detail-haji', compact('package', 'dbPackage', 'kamarPrices'));
        }

        return view('pages.detail-paket', compact('package', 'dbPackage', 'kamarPrices'));
    }

    /**
     * Tentang Kami
     */
    public function tentang(): View
    {
        return view('pages.tentang');
    }

    /**
     * Tanya Jawab (FAQ)
     */
    public function faq(): View
    {
        return view('pages.faq');
    }
}
