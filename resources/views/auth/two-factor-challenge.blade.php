@extends('layouts.auth')

@section('title', 'Verifikasi Dua Langkah (2FA)')

@section('content')
<div class="card-header">
    <div style="font-size: 32px; margin-bottom: 8px;">🛡️</div>
    <h1 class="card-title">Verifikasi Dua Langkah</h1>
    <p class="card-desc">Masukkan kode autentikator dari aplikasi Google Authenticator Anda</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Verifikasi Gagal:</strong>
        <ul style="margin-left: 18px; margin-top: 4px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('two-factor.login') }}">
    @csrf

    <div class="form-group" id="code-group">
        <label for="code" class="form-label">Kode Otentikasi (6 Digit)</label>
        <input 
            type="text" 
            name="code" 
            id="code" 
            class="form-control" 
            inputmode="numeric" 
            autofocus 
            autocomplete="one-time-code" 
            placeholder="123456"
        >
    </div>

    <div class="form-group" id="recovery-group" style="display: none;">
        <label for="recovery_code" class="form-label">Kode Pemulihan Darurat (Recovery Code)</label>
        <input 
            type="text" 
            name="recovery_code" 
            id="recovery_code" 
            class="form-control" 
            autocomplete="one-time-code" 
            placeholder="xxxx-xxxx-xxxx"
        >
    </div>

    <button type="submit" class="btn-primary" style="margin-top: 8px;">
        Verifikasi & Lanjutkan
    </button>
</form>

<div class="card-footer">
    <button type="button" id="toggle-recovery" style="background: none; border: none; color: var(--primary); font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: underline;">
        Gunakan kode pemulihan darurat
    </button>
</div>

<script>
    const toggleBtn = document.getElementById('toggle-recovery');
    const codeGroup = document.getElementById('code-group');
    const recoveryGroup = document.getElementById('recovery-group');
    let usingRecovery = false;

    toggleBtn.addEventListener('click', () => {
        usingRecovery = !usingRecovery;
        if (usingRecovery) {
            codeGroup.style.display = 'none';
            recoveryGroup.style.display = 'block';
            toggleBtn.textContent = 'Gunakan kode autentikasi 6 digit';
            document.getElementById('recovery_code').focus();
        } else {
            codeGroup.style.display = 'block';
            recoveryGroup.style.display = 'none';
            toggleBtn.textContent = 'Gunakan kode pemulihan darurat';
            document.getElementById('code').focus();
        }
    });
</script>
@endsection