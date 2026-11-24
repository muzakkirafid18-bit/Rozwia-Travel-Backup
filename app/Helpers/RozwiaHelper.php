<?php

namespace App\Helpers;

use App\Models\Booking;
use App\Models\Invoice;

class RozwiaHelper
{
    /**
     * Format angka ke mata uang Rupiah
     */
    public static function formatRupiah(float|int|string|null $amount, bool $withPrefix = true): string
    {
        $numeric = (float) ($amount ?? 0);
        $formatted = number_format($numeric, 0, ',', '.');
        return $withPrefix ? 'Rp ' . $formatted : $formatted;
    }

    /**
     * Format tanggal seragam d/m/Y (Tanggal/Bulan/Tahun)
     */
    public static function formatDate(mixed $date, string $format = 'd/m/Y', string $fallback = '-'): string
    {
        if (blank($date)) {
            return $fallback;
        }

        try {
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Throwable $e) {
            return (string) $date;
        }
    }

    /**
     * Format ringkas ke satuan Juta (contoh: 34.5 Jt, 50 Jt)
     */
    public static function formatJuta(float|int|string|null $amount): string
    {
        $numeric = (float) ($amount ?? 0);
        if ($numeric >= 1000000) {
            $val = $numeric / 1000000;
            return (fmod($val, 1) === 0.0 ? number_format($val, 0, ',', '.') : number_format($val, 1, ',', '.')) . ' Jt';
        }
        return self::formatRupiah($amount);
    }

    /**
     * Generate Kode Booking Unik (Format: BK-YYYYMM-XXXX)
     */
    public static function generateBookingCode(): string
    {
        $prefix = 'BK-' . date('Ym') . '-';
        $latest = Booking::where('kode_booking', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('kode_booking');

        if ($latest) {
            $number = (int) substr($latest, strlen($prefix)) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate Nomor Invoice Unik (Format: INV-YYYYMM-XXXX)
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Ym') . '-';
        $latest = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice_number');

        if ($latest) {
            $number = (int) substr($latest, strlen($prefix)) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Lokasi file profil perusahaan (terpisah saat testing agar data asli tidak terhapus)
     */
    public static function companyProfilePath(): string
    {
        return app()->runningUnitTests()
            ? storage_path('app/company_profile_testing.json')
            : storage_path('app/company_profile.json');
    }

    /**
     * Ambil profil perusahaan terbaru yang dikelola Supervisor (DB `perusahaan` atau fallback storage)
     */
    public static function companyProfile(): array
    {
        try {
            $perusahaan = \App\Models\Perusahaan::first();
            if ($perusahaan) {
                return [
                    'nama_perusahaan' => $perusahaan->nama_perusahaan,
                    'slogan'          => 'Penyelenggara Umroh dan Haji Resmi',
                    'no_izin'         => $perusahaan->no_izin ?? 'PPIU No. 1023/2021',
                    'email'           => $perusahaan->email_perusahaan,
                    'telepon'         => $perusahaan->telepon_perusahaan,
                    'alamat'          => $perusahaan->alamat_perusahaan,
                    'website'         => 'https://rozwia.travel',
                    'nama_bank'       => $perusahaan->nama_bank,
                    'nomor_rekening'  => $perusahaan->nomor_rekening,
                    'atas_nama'       => $perusahaan->atas_nama,
                ];
            }
        } catch (\Throwable $e) {
            // fallback if DB not ready or migration running
        }

        $default = [
            'nama_perusahaan' => 'Rozwia Islamic Travel',
            'slogan'          => 'Penyelenggara Umroh dan Haji Resmi',
            'no_izin'         => 'PPIU No. 1023/2021',
            'email'           => 'Rozwia@gmail.com',
            'telepon'         => '0895-1882-9438',
            'alamat'          => 'Graha Kartika Pratama Blok F2 Nomor 11 Kelurahan Bojong Baru Kecamatan Bojong Gede Kabupaten Bogor Jawa Barat',
            'website'         => 'https://rozwia.travel',
        ];

        $path = self::companyProfilePath();
        if (file_exists($path)) {
            $data = json_decode((string) file_get_contents($path), true);
            if (is_array($data)) {
                return array_merge($default, $data);
            }
        }

        return $default;
    }

    /**
     * Sapaan Bapak / Ibu sesuai gender pada data anggota jamaah.
     */
    public static function salutation(?string $gender): string
    {
        return match (strtolower(trim((string) $gender))) {
            'laki-laki', 'pria', 'l' => 'Bapak',
            'perempuan', 'wanita', 'p' => 'Ibu',
            default => 'Bapak/Ibu',
        };
    }

    /**
     * Ambil sapaan Bapak/Ibu dari JamaahDetail (atau booking terbaru milik User).
     */
    public static function userSalutation(\App\Models\User $user, ?\App\Models\JamaahDetail $detail = null): string
    {
        if ($detail && filled($detail->jenis_kelamin)) {
            return self::salutation($detail->jenis_kelamin);
        }

        $primaryDetail = $user->bookings()
            ->with('jamaahDetails')
            ->latest()
            ->first()
            ?->jamaahDetails
            ->first(fn ($d) => filled($d->jenis_kelamin));

        return self::salutation($primaryDetail?->jenis_kelamin);
    }

    /**
     * URL login dinamis mengikuti host/IP & base path request aktif agar tombol email selalu valid.
     */
    public static function dynamicLoginUrl(): string
    {
        $req = request();
        if (!app()->runningInConsole() && $req && $req->getHost()) {
            $base = rtrim($req->getSchemeAndHttpHost() . $req->getBaseUrl(), '/');
            return $base . '/login/direct';
        }

        return route('login.direct');
    }

    /**
     * Hitung total pembayaran yang sudah diverifikasi Berhasil untuk sebuah Booking.
     */
    public static function bookingTotalPaid(Booking $booking): float
    {
        $booking->loadMissing('invoices.riwayatPembayarans');

        return (float) $booking->invoices
            ->flatMap(fn ($inv) => $inv->riwayatPembayarans)
            ->where('status', 'Berhasil')
            ->sum('amount');
    }

    /**
     * Cek apakah tagihan Booking sudah Lunas 100%.
     */
    public static function isBookingLunas(Booking $booking): bool
    {
        $totalHarga = (float) ($booking->total_harga ?? 0);
        if ($totalHarga <= 0) {
            return false;
        }

        return self::bookingTotalPaid($booking) >= $totalHarga;
    }
}
