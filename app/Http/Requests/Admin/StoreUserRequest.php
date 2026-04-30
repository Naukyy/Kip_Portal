<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        
        return [
            'employee_code' => ['required', 'string', 'max:50', 'unique:users,employee_code,' . $userId],
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password' => ['nullable', 'string', 'confirmed'],
            'role' => ['required', 'in:Admin,Management,Trainer Senior,Trainer Junior'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_code.required' => 'Kode Pegawai wajib diisi.',
            'employee_code.unique' => 'Kode Pegawai sudah digunakan.',
            'name.required' => 'Nama Lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'email.email' => 'Format email tidak valid.',
            'role.required' => 'Posisi/Jabatan wajib dipilih.',
            'role.in' => 'Posisi/Jabatan tidak valid.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}
