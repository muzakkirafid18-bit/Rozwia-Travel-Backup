<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti DP Diterima - ROZWIA</title>
</head>
<body style="margin:0; padding:0; background-color:#F4EFE6; font-family:'Segoe UI', Tahoma, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#F4EFE6; padding:32px 16px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08);">
                    
                    <!-- header halaman -->
                    <tr>
                        <td style="background-color:#0B3B24; padding:32px; text-align:center;">
                            <h1 style="color:#ffffff; font-size:24px; margin:0;">🕌 ROZWIA Islamic Travel</h1>
                            <p style="color:#D4B996; font-size:12px; margin:8px 0 0; letter-spacing:2px;">BUKTI PEMBAYARAN DP DITERIMA</p>
                        </td>
                    </tr>

                    <!-- isi email -->
                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:16px; color:#1a1a1a; margin:0 0 8px;">Assalamu'alaikum Warahmatullahi Wabarakatuh, <strong>{{ $sapaan ?? 'Bapak/Ibu' }} {{ $user->name }}</strong></p>
                            <p style="font-size:14px; color:#555; line-height:1.6; margin:0 0 24px;">
                                Terima kasih, bukti transfer pembayaran DP {{ $sapaan ?? 'Bapak/Ibu' }} telah kami terima dan saat ini sedang dalam proses verifikasi oleh tim admin ROZWIA Islamic Travel.
                            </p>

                            <table width="100%" cellpadding="8" cellspacing="0" style="background-color:#f8f6f2; border-radius:12px; font-size:14px; margin-bottom:24px;">
                                <tr>
                                    <td style="color:#888; width:180px;">Kode Booking</td>
                                    <td style="font-weight:bold; color:#0B3B24;">{{ $booking->kode_booking }}</td>
                                </tr>
                                <tr>
                                    <td style="color:#888;">No. Invoice</td>
                                    <td style="font-weight:bold; color:#1a1a1a;">{{ $invoice->invoice_number }}</td>
                                </tr>
                                <tr>
                                    <td style="color:#888;">Status</td>
                                    <td style="font-weight:bold; color:#d97706;">⏳ Menunggu Verifikasi Admin</td>
                                </tr>
                            </table>

                            <div style="text-align:center; margin:24px 0;">
                                <a href="{{ $loginUrl ?? route('login.direct') }}" target="_blank" style="display:inline-block; background-color:#0B3B24; color:#ffffff; padding:12px 28px; border-radius:8px; text-decoration:none; font-weight:bold; font-size:14px;">
                                    Login ke ROZWIA
                                </a>
                            </div>

                            <p style="font-size:13px; color:#888; line-height:1.6;">
                                Proses verifikasi biasanya membutuhkan waktu <strong>1×24 jam</strong> kerja. Anda akan menerima email notifikasi segera setelah pembayaran diverifikasi.
                            </p>
                        </td>
                    </tr>

                    <!-- footer email -->
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
