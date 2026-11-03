<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'deadline' => ['required', 'date_format:Y-m-d'],
            'status' => ['nullable', 'string', 'in:todo,done'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'title.max' => 'Judul maksimal 255 karakter.',
            'deadline.required' => 'Deadline wajib diisi.',
            'deadline.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'status.in' => 'Status harus berupa todo atau done.',
        ];
    }
}
