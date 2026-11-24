<?php

namespace App\Http\Requests\Supervisor;

use Illuminate\Foundation\Http\FormRequest;

class StoreRekeningPerusahaanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isSupervisor();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_bank' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9\s\.\-\(\)]+$/'],
            'nomor_rekening' => ['required', 'string', 'max:50', 'regex:/^[0-9\-]+$/'],
            'atas_nama' => ['required', 'string', 'max:150', 'regex:/^[a-zA-Z\s\.\,\'\-]+$/'],
            'nama_perusahaan' => ['required', 'string', 'max:150'],
            'alamat_perusahaan' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_bank.required' => 'Nama bank wajib diisi.',
            'nomor_rekening.required' => 'Nomor rekening wajib diisi.',
            'nomor_rekening.regex' => 'Nomor rekening hanya boleh berisi angka dan tanda hubung.',
            'atas_nama.required' => 'Nama pemilik rekening wajib diisi.',
            'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
            'status.in' => 'Status rekening harus active atau inactive.',
        ];
    }
}
