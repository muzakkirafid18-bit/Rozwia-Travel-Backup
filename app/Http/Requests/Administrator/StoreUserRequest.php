<?php

namespace App\Http\Requests\Administrator;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'          => is_string($this->name) ? trim($this->name) : $this->name,
            'email'         => is_string($this->email) ? Str::lower(trim($this->email)) : $this->email,
            'nomor_telepon' => is_string($this->nomor_telepon) && trim($this->nomor_telepon) !== '' ? trim($this->nomor_telepon) : null,
            'role'          => is_string($this->role) ? Str::lower(trim($this->role)) : $this->role,
            'status_akun'   => is_string($this->status_akun) ? Str::lower(trim($this->status_akun)) : 'active',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'min:3', 'max:120'],
            'email'         => ['required', 'string', 'email:rfc', 'max:150', Rule::unique('users', 'email')],
            'nomor_telepon' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'role'          => ['required', 'string', Rule::in(User::ROLES)],
            'status_akun'   => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'          => 'Nama lengkap wajib diisi.',
            'name.min'               => 'Nama minimal 3 karakter.',
            'email.required'         => 'Alamat email wajib diisi.',
            'email.email'            => 'Format email tidak valid.',
            'email.unique'           => 'Email tersebut sudah terdaftar pada akun lain.',
            'nomor_telepon.regex'    => 'Format nomor telepon tidak valid (8-20 digit).',
            'role.in'                => 'Role yang dipilih tidak valid.',
            'status_akun.in'         => 'Status akun harus active atau inactive.',
        ];
    }
}
