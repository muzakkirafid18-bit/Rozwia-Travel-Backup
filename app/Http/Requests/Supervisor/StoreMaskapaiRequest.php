<?php

namespace App\Http\Requests\Supervisor;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaskapaiRequest extends FormRequest
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
            'nama_maskapai' => ['required', 'string', 'max:150', 'unique:maskapai,nama_maskapai'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_maskapai.required' => 'Nama maskapai wajib diisi.',
            'nama_maskapai.unique' => 'Nama maskapai sudah terdaftar.',
        ];
    }
}
