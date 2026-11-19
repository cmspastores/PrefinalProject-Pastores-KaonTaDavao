<?php

namespace App\Http\Requests;

use App\Models\Eatery;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $eatery = $this->route('eatery');

        return $eatery instanceof Eatery && $eatery->is_open;
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
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:10', 'max:1000'],
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
            'user_id.required' => 'Choose who is writing this review.',
            'user_id.exists' => 'The selected reviewer could not be found.',
            'rating.between' => 'Ratings go from 1 to 5 stars only.',
            'body.min' => 'Kaon Ta Pastores: reviews need at least 10 characters.',
        ];
    }
}
