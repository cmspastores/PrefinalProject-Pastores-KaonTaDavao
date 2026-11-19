<?php

namespace App\Http\Requests;

use App\Models\Eatery;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveEateryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
        $eatery = $this->route('eatery');
        $required = $this->isMethod('PATCH') ? 'sometimes' : 'required';
        $permitId = $eatery instanceof Eatery
            ? $eatery->permit()->value('id')
            : null;

        return [
            'district_id' => [$required, 'integer', 'exists:districts,id'],
            'name' => [
                $required,
                'string',
                'max:100',
                Rule::unique('eateries', 'name')->ignore($eatery),
            ],
            'slug' => [
                $required,
                'string',
                'max:255',
                Rule::unique('eateries', 'slug')->ignore($eatery),
            ],
            'address' => [$required, 'string', 'max:255'],
            'price_range' => [$required, Rule::in(['budget', 'mid', 'splurge'])],
            'specialty' => ['nullable', 'string', 'max:255'],
            'is_open' => [$required, 'boolean'],
            'features' => ['sometimes', 'array'],
            'features.*' => ['integer', 'exists:features,id'],
            'permit_no' => [
                'nullable',
                'string',
                'max:255',
                'required_with:expires_on',
                Rule::unique('permits', 'permit_no')->ignore($permitId),
            ],
            'expires_on' => ['nullable', 'date', 'required_with:permit_no'],
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
            'district_id.required' => 'Choose a district for the eatery.',
            'district_id.integer' => 'Choose a valid district.',
            'district_id.exists' => 'The selected district could not be found.',
            'name.required' => 'Enter the eatery name.',
            'name.string' => 'The eatery name must be text.',
            'name.max' => 'The eatery name may not exceed 100 characters.',
            'name.unique' => 'An eatery with this name already exists.',
            'slug.required' => 'Enter a URL slug for the eatery.',
            'slug.string' => 'The eatery slug must be text.',
            'slug.max' => 'The eatery slug may not exceed 255 characters.',
            'slug.unique' => 'This eatery slug is already in use.',
            'address.required' => 'Enter the eatery address.',
            'address.string' => 'The address must be text.',
            'address.max' => 'The address may not exceed 255 characters.',
            'price_range.required' => 'Choose a price range.',
            'price_range.in' => 'Choose budget, mid, or splurge for the price range.',
            'specialty.string' => 'The specialty must be text.',
            'specialty.max' => 'The specialty may not exceed 255 characters.',
            'is_open.required' => 'Choose whether the eatery is currently open.',
            'is_open.boolean' => 'Choose a valid open or closed status.',
            'features.array' => 'Choose features from the available list.',
            'features.*.integer' => 'Each selected feature must be valid.',
            'features.*.exists' => 'One of the selected features could not be found.',
            'permit_no.string' => 'The permit number must be text.',
            'permit_no.max' => 'The permit number may not exceed 255 characters.',
            'permit_no.required_with' => 'Enter a permit number when an expiry date is provided.',
            'permit_no.unique' => 'This permit number is already in use.',
            'expires_on.date' => 'Enter a valid permit expiry date.',
            'expires_on.required_with' => 'Enter an expiry date when a permit number is provided.',
        ];
    }
}
