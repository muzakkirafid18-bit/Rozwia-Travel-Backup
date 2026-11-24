<?php

namespace App\Http\Requests\Supervisor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRekeningPerusahaanRequest extends FormRequest
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
}
