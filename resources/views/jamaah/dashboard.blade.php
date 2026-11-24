@extends('layouts.dashboard')

@section('title', 'Dashboard Jamaah')
@section('page-title', 'Portal Jamaah ROZWIA')

@section('sidebar-menu')
    <div class="menu-title">Menu Jamaah</div>
    <a href="{{ route('jamaah.dashboard') }}" class="nav-item active">🏠 Beranda Jamaah</a>
    <a href="#" class="nav-item">🧾 Pemesanan Saya</a>
    <a href="#" class="nav-item">📁 Upload Dokumen (KTP/Paspor)</a>
    <a href="#" class="nav-item">📍 Tracking Keberangkatan</a>
@endsection

@section('content')
<div class="card" style="border-left: 5px solid #16a34a;">
    <h2 style="font-size: 18px; margin-bottom: 6px;">Ahlan Wa Sahlan, {{ Auth::user()->name }}! 🕋</h2>
    <p style="color: var(--text-muted); font-size: 14px;">
        Selamat datang di portal jamaah ROZWIA Islamic Travel. Anda dapat melengkapi berkas dokumen persyaratan ibadah dan memantau status keberangkatan di sini.
    </p>
</div>

<div class="card">
    <h3 style="font-size: 16px; margin-bottom: 12px;">Alur Tahapan Ibadah Anda</h3>
    <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <span style="background: #e0f2fe; color: #0369a1; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600;">1. Registrasi Akun ✅</span>
        <span style="background: #fef3c7; color: #92400e; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600;">2. Pembayaran DP (Aktif) ⏳</span>
        <span style="background: #f1f5f9; color: #64748b; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600;">3. Upload Berkas Paspor & KTP</span>
        <span style="background: #f1f5f9; color: #64748b; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600;">4. Pelunasan & Manifes</span>
        <span style="background: #f1f5f9; color: #64748b; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600;">5. Berangkat Menuju Baitullah</span>
    </div>
</div>
@endsection