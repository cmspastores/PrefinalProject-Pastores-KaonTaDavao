<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaveReviewRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['required', 'string'],
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
            'user_id.required' => 'Choose a reviewer.',
            'user_id.integer' => 'Choose a valid reviewer.',
            'user_id.exists' => 'The selected reviewer could not be found.',
            'rating.required' => 'Choose a rating.',
            'rating.integer' => 'Choose a whole-number rating.',
            'rating.min' => 'The rating must be at least 1.',
            'rating.max' => 'The rating cannot be greater than 5.',
            'body.required' => 'Write a review before submitting.',
            'body.string' => 'The review must be text.',
        ];
    }
}
