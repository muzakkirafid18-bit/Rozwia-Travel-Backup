<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Diverifikasi - ROZWIA</title>
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
                            <p style="color:#22c55e; font-size:12px; margin:8px 0 0; letter-spacing:2px;">✅ PEMBAYARAN DP DIVERIFIKASI</p>
                        </td>
                    </tr>

                    <!-- isi email -->
                    <tr>
                        <td style="padding:32px;">
                            <p style="font-size:16px; color:#1a1a1a; margin:0 0 8px;">Assalamu'alaikum Warahmatullahi Wabarakatuh, <strong>{{ $sapaan ?? 'Bapak/Ibu' }} {{ $user->name }}</strong></p>
                            <p style="font-size:14px; color:#555; line-height:1.6; margin:0 0 24px;">
                                @if(($paymentType ?? 'DP') === 'Lunas')
                                    Alhamdulillah! Pembayaran <strong>Pelunasan</strong> {{ $sapaan ?? 'Bapak/Ibu' }} telah <strong>diverifikasi dan disetujui</strong> oleh admin ROZWIA Islamic Travel. Seluruh tagihan paket ibadah {{ $sapaan ?? 'Bapak/Ibu' }} kini telah <strong>LUNAS SEPENUHNYA</strong>.
                                @elseif(($paymentType ?? 'DP') === 'Cicilan')
                                    Alhamdulillah! Pembayaran <strong>Cicilan</strong> {{ $sapaan ?? 'Bapak/Ibu' }} telah <strong>diverifikasi dan disetujui</strong> oleh admin ROZWIA Islamic Travel.
                                @else
                                    Alhamdulillah! Pembayaran <strong>Uang Muka (DP)</strong> {{ $sapaan ?? 'Bapak/Ibu' }} telah <strong>diverifikasi dan disetujui</strong> oleh admin ROZWIA Islamic Travel. Akun Portal Jamaah {{ $sapaan ?? 'Bapak/Ibu' }} kini telah <strong>aktif</strong>.
                                @endif
                            </p>

                            <table width="100%" cellpadding="8" cellspacing="0" style="background-color:#f0fdf4; border-radius:12px; font-size:14px; margin-bottom:24px; border:1px solid #bbf7d0;">
                                <tr>
                                    <td style="color:#888; width:180px;">Kode Booking</td>
                                    <td style="font-weight:bold; color:#0B3B24;">{{ $booking->kode_booking }}</td>
                                </tr>
                                <tr>
                                    <td style="color:#888;">Status Akun</td>
                                    <td style="font-weight:bold; color:#16a34a;">🟢 AKTIF</td>
                                </tr>
                                <tr>
                                    <td style="color:#888;">Langkah Selanjutnya</td>
                                    <td style="font-weight:bold; color:#0B3B24;">Silakan login dan wajib mengganti password, setelah itu mengupload berkas dan dokumen berupa KTP, KK, dan Paspor (jika memiliki paspor)</td>
                                </tr>
                            </table>

                            <div style="background-color:#fef3c7; border:1px solid #fde68a; border-radius:12px; padding:16px; margin-bottom:24px; font-size:14px; color:#92400e;">
                                <p style="margin:0 0 6px; font-weight:bold; font-size:14px;">🔐 Informasi Login Akun Portal Jamaah:</p>
                                <p style="margin:2px 0;">Alamat Email: <strong>{{ $user->email }}</strong></p>
                                @if(!empty($plainPassword))
                                    <p style="margin:4px 0;">Password Sementara: <strong style="font-family:monospace; font-size:15px; color:#0B3B24; background:#fff; padding:2px 8px; border-radius:4px;">{{ $plainPassword }}</strong></p>
                                @endif
                                <p style="margin:6px 0 0; font-size:12px; color:#b45309;">💡 Demi keamanan akun {{ $sapaan ?? 'Bapak/Ibu' }}, sistem akan meminta untuk mengganti password sementara ini pada saat login pertama.</p>
                            </div>

                            <div style="background-color:#0B3B24; color:#fff; padding:20px; border-radius:12px; text-align:center; margin-bottom:24px;">
                                <p style="font-size:14px; margin:0 0 12px; color:#D4B996;">Silakan login ke website ROZWIA untuk melengkapi dokumen perjalanan:</p>
                                <a href="{{ $loginUrl ?? route('login') }}" target="_blank" style="display:inline-block; background-color:#d97706; color:#ffffff; padding:12px 32px; border-radius:8px; text-decoration:none; font-weight:bold; font-size:14px;">
                                    Login ke ROZWIA
                                </a>
                            </div>

                            <p style="font-size:13px; color:#888; line-height:1.6;">
                                Setelah login, buka menu <strong>"Dokumen Persyaratan"</strong> untuk mengunggah scan KTP, Kartu Keluarga, dan Paspor Anda.
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
    @if(!empty($trackingToken))
        <img src="{{ route('email.track', $trackingToken) }}" width="1" height="1" alt="" style="display:none;width:1px;height:1px;">
    @endif
</body>
</html>
