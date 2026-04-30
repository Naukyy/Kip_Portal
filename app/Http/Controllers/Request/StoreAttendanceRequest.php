<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'date'                          => 'required|date',
            'attendances'                   => 'required|array',
            'attendances.*.status'          => 'required|in:Attend,Permission,Absent',
            'attendances.*.substitute_id'   => 'nullable|exists:users,id',
            'attendances.*.notes'           => 'nullable|string|max:255',
        ];
    }
}