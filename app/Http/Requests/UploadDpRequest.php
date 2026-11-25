<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadDpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Bisa diakses dari alur publik setelah booking maupun dari dashboard
    }

    public function rules(): array
    {
        return [
            'bukti_dp' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'amount'   => ['required', 'numeric', 'min:1000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'bukti_dp.required' => 'Bukti transfer DP wajib diunggah.',
            'bukti_dp.file'     => 'Berkas yang diunggah tidak valid.',
            'bukti_dp.mimes'    => 'Berkas harus berformat JPG, PNG, atau PDF.',
            'bukti_dp.max'      => 'Ukuran berkas tidak boleh melebihi 10MB.',
            'amount.required'   => 'Nominal pembayaran wajib diisi.',
            'amount.min'        => 'Minimal pembayaran DP adalah Rp 1.000.000.',
        ];
    }
}
