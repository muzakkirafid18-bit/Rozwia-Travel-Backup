@extends('layouts.auth')

@section('title', 'Daftar Akun Jamaah')

@section('content')
<div class="card-header">
    <h1 class="card-title">Daftar Akun Baru</h1>
    <p class="card-desc">Lengkapi formulir untuk membuat akun jamaah</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Pendaftaran Gagal:</strong>
        <ul style="margin-left: 18px; margin-top: 4px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="form-group">
        <label for="name" class="form-label">Nama Lengkap Sesuai KTP</label>
        <input 
            type="text" 
            name="name" 
            id="name" 
            class="form-control @error('name') is-invalid @enderror" 
            value="{{ old('name') }}" 
            required 
            autofocus 
            placeholder="Contoh: Muhammad Gunawan"
        >
        @error('name')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="email" class="form-label">Alamat Email</label>
        <input 
            type="email" 
            name="email" 
            id="email" 
            class="form-control @error('email') is-invalid @enderror" 
            value="{{ old('email') }}" 
            required 
            placeholder="nama@email.com"
        >
        @error('email')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="nomor_telepon" class="form-label">Nomor WhatsApp / Telepon</label>
        <input 
            type="text" 
            name="nomor_telepon" 
            id="nomor_telepon" 
            class="form-control @error('nomor_telepon') is-invalid @enderror" 
            value="{{ old('nomor_telepon') }}" 
            required 
            placeholder="Contoh: 081234567890"
        >
        @error('nomor_telepon')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input 
            type="password" 
            name="password" 
            id="password" 
            class="form-control @error('password') is-invalid @enderror" 
            required 
            placeholder="Minimal 8 karakter"
        >
        @error('password')
            <span class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
        <input 
            type="password" 
            name="password_confirmation" 
            id="password_confirmation" 
            class="form-control" 
            required 
            placeholder="Ulangi password di atas"
        >
    </div>

    <button type="submit" class="btn-primary" style="margin-top: 8px;">
        Daftar Sekarang
    </button>
</form>

<div class="card-footer">
    Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
</div>
@endsection