<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|min:10|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Content is required for analysis.',
            'content.min' => 'Content must be at least 10 characters.',
            'content.max' => 'Content cannot exceed 5000 characters.',
        ];
    }
}
