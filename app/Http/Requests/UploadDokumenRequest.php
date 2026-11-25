<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadDokumenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'jamaah_detail_id' => ['nullable', 'integer'],
            'passport_number'  => ['required', 'string', 'min:5', 'max:50'],
            'ktp'              => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'kk'               => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'paspor'           => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'pas_foto'         => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'buku_nikah'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'passport_number.required' => 'Nomor Paspor wajib diisi sebelum menyimpan berkas.',
            'passport_number.min'      => 'Nomor Paspor minimal 5 karakter.',
            'ktp.mimes'        => 'File KTP harus berformat JPG, PNG, atau PDF.',
            'ktp.max'          => 'Ukuran file KTP tidak boleh melebihi 10MB.',
            'kk.mimes'         => 'File KK harus berformat JPG, PNG, atau PDF.',
            'kk.max'           => 'Ukuran file KK tidak boleh melebihi 10MB.',
            'paspor.mimes'     => 'File Paspor harus berformat JPG, PNG, atau PDF.',
            'paspor.max'       => 'Ukuran file Paspor tidak boleh melebihi 10MB.',
            'pas_foto.mimes'   => 'File Pas Foto 4x6 harus berformat JPG, PNG, atau PDF.',
            'pas_foto.max'     => 'Ukuran file Pas Foto 4x6 tidak boleh melebihi 10MB.',
            'buku_nikah.mimes' => 'File Buku Nikah / Akta Lahir harus berformat JPG, PNG, atau PDF.',
            'buku_nikah.max'   => 'Ukuran file Buku Nikah / Akta Lahir tidak boleh melebihi 10MB.',
        ];
    }
}
