<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Halaman publik, siapa saja boleh mendaftar
    }

    public function rules(): array
    {
        return [
            'nama_lengkap'   => ['required', 'string', 'max:255'],
            'jenis_kelamin'  => ['nullable', 'string', 'in:Laki-laki,Perempuan'],
            'email'          => ['required', 'email', 'max:255'],
            'nomor_telepon'  => ['required', 'string', 'max:25', 'regex:/^(?:\+?62|0)?[0-9\-\s\(\)]{8,18}$/'],
            'alamat'         => ['nullable', 'string', 'max:1000'],
            'tipe_kamar'       => ['nullable', 'string', 'in:double,triple,quad'],
            'jumlah_jamaah'    => ['nullable', 'integer', 'min:1', 'max:50'],
            'jadwal_id'        => ['nullable', 'exists:jadwal,id'],
            'anggota_jamaah'   => ['nullable', 'array'],
            'anggota_jamaah.*' => ['nullable', 'string', 'max:255'],
            'anggota_gender'   => ['nullable', 'array'],
            'anggota_gender.*' => ['nullable', 'string', 'in:Laki-laki,Perempuan'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_lengkap.required'  => 'Nama lengkap wajib diisi.',
            'email.required'         => 'Alamat email wajib diisi.',
            'email.email'            => 'Format email tidak valid.',
            'nomor_telepon.required' => 'Nomor WhatsApp wajib diisi.',
            'nomor_telepon.regex'    => 'Nomor WhatsApp tidak boleh kosong dan minimal 8 digit angka setelah +62.',
            'jadwal_id.exists'       => 'Jadwal keberangkatan tidak ditemukan.',
        ];
    }
}
