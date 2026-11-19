<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FilterEateriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'nullable', 'string', 'max:255'],
            'district' => ['sometimes', 'nullable', 'integer', 'exists:districts,id'],
            'feature' => ['sometimes', 'nullable', 'string', 'exists:features,name'],
        ];
    }

    /**
     * Get custom messages for the validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'q.string' => 'Enter search text.',
            'q.max' => 'Search text may not exceed 255 characters.',
            'district.integer' => 'Choose a valid district.',
            'district.exists' => 'The selected district could not be found.',
            'feature.string' => 'Choose a valid feature.',
            'feature.exists' => 'The selected feature could not be found.',
        ];
    }
}
