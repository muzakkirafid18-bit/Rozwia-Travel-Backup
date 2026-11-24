<?php

namespace App\Http\Requests\Supervisor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaskapaiRequest extends FormRequest
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
        $maskapaiId = $this->route('maskapai') ? $this->route('maskapai')->id : null;

        return [
            'nama_maskapai' => [
                'required',
                'string',
                'max:150',
                Rule::unique('maskapai', 'nama_maskapai')->ignore($maskapaiId),
            ],
        ];
    }
}
