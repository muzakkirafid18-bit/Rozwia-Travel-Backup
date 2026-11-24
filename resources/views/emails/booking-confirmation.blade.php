<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pendaftaran ROZWIA</title>
</head>
<body style="margin:0; padding:0; background-color:#F4EFE6; font-family:'Segoe UI', Tahoma, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F4EFE6; padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#0B3B24; padding:32px; text-align:center;">
                            <h1 style="color:#ffffff; font-size:24px; margin:0;">🕌 ROZWIA Islamic Travel</h1>
                            <p style="color:#D4B996; font-size:12px; margin:8px 0 0; letter-spacing:2px;">KONFIRMASI PENDAFTARAN</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:16px; color:#1a1a1a; margin:0 0 8px;">Assalamu'alaikum Warahmatullahi Wabarakatuh, <strong>{{ $sapaan ?? 'Bapak/Ibu' }} {{ $user->name }}</strong></p>
                            <p style="font-size:14px; color:#555; line-height:1.6; margin:0 0 24px;">
                                Alhamdulillah, terima kasih telah mempercayakan perjalanan ibadah bersama ROZWIA Islamic Travel. Pendaftaran {{ $sapaan ?? 'Bapak/Ibu' }} telah kami terima dengan rincian sebagai berikut:
                            </p>

                            <!-- Info Booking -->
                            <table width="100%" cellpadding="8" cellspacing="0" style="background-color:#f8f6f2; border-radius:12px; font-size:14px; margin-bottom:24px;">
                                <tr>
                                    <td style="color:#888; width:180px;">Kode Booking</td>
                                    <td style="font-weight:bold; color:#0B3B24;">{{ $booking->kode_booking }}</td>
                                </tr>
                                <tr>
                                    <td style="color:#888;">Paket</td>
                                    <td style="font-weight:bold; color:#1a1a1a;">{{ $paket->nama_paket }}</td>
                                </tr>
                                <tr>
                                    <td style="color:#888;">Total Biaya</td>
                                    <td style="font-weight:bold; color:#1a1a1a;">{{ \App\Helpers\RozwiaHelper::formatRupiah($booking->total_harga) }}</td>
                                </tr>
                                <tr>
                                    <td style="color:#888;">Nominal DP</td>
                                    <td style="font-weight:bold; color:#d97706;">{{ \App\Helpers\RozwiaHelper::formatRupiah($invoice->amount) }}</td>
                                </tr>
                                <tr>
                                    <td style="color:#888;">Batas Pembayaran</td>
                                    <td style="font-weight:bold; color:#dc2626;">{{ $invoice->batas_pembayaran->format('d/m/Y, H:i') }} WIB</td>
                                </tr>
                            </table>

                            <!-- Info Rekening -->
                            @if($rekening)
                            <div style="background-color:#0B3B24; color:#fff; padding:20px; border-radius:12px; margin-bottom:24px;">
                                <p style="font-size:12px; color:#D4B996; margin:0 0 8px; letter-spacing:1px;">REKENING TUJUAN TRANSFER</p>
                                <p style="font-size:18px; font-weight:bold; margin:0;">{{ $rekening->nama_bank }}</p>
                                <p style="font-size:22px; font-weight:bold; margin:4px 0; color:#E5D3B3;">{{ $rekening->nomor_rekening }}</p>
                                <p style="font-size:14px; margin:0;">a.n. {{ $rekening->atas_nama }}</p>
                            </div>
                            @endif

                            <!-- Kredensial Login -->
                            <div style="background-color:#fef3c7; padding:20px; border-radius:12px; border-left:4px solid #d97706; margin-bottom:24px;">
                                <p style="font-size:13px; font-weight:bold; color:#92400e; margin:0 0 8px;">🔐 Kredensial Login Anda (Sementara)</p>
                                <p style="font-size:14px; color:#78350f; margin:2px 0;">Email: <strong>{{ $user->email }}</strong></p>
                                <p style="font-size:14px; color:#78350f; margin:2px 0;">Password: <strong>{{ $tempPassword }}</strong></p>
                                <p style="font-size:12px; color:#92400e; margin:8px 0 0;">⚠️ Akun Anda baru aktif setelah admin memverifikasi pembayaran DP.</p>
                            </div>

                            <div style="text-align:center; margin:24px 0;">
                                <a href="{{ $loginUrl ?? route('login.direct') }}" target="_blank" style="display:inline-block; background-color:#0B3B24; color:#ffffff; padding:12px 28px; border-radius:8px; text-decoration:none; font-weight:bold; font-size:14px;">
                                    Login ke ROZWIA
                                </a>
                            </div>

                            <p style="font-size:13px; color:#888; line-height:1.6;">
                                Setelah melakukan transfer, silakan login ke website ROZWIA dan upload bukti transfer DP melalui menu <strong>"Formulir Pemesanan & DP"</strong>.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f8f6f2; padding:20px; text-align:center; border-top:1px solid #e5e2dc;">
                            <p style="font-size:12px; color:#999; margin:0;">© {{ date('Y') }} ROZWIA Islamic Travel — PPIU Resmi Kementerian Agama RI</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
