<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        
        return [
            'employee_code' => ['required', 'string', 'max:50', 'unique:users,employee_code,' . $userId],
            'name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            // Password optional - handled in withValidator for conditional required
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(['Admin', 'Management', 'Trainer Senior', 'Trainer Junior'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
    
    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
            
            // When is_active is not present in request (checkbox unchecked), set it to false
            if (!$this->has('is_active')) {
                $this->merge(['is_active' => false]);
            }
            
            // On create, password is required
            if (!$isUpdate && !$this->filled('password')) {
                $validator->errors()->add('password', 'Password wajib diisi.');
            }
        });
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
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }
}
