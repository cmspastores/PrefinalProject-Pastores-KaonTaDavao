<?php

namespace App\Http\Requests;

use App\Models\Eatery;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEateryRequest extends FormRequest
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
        $nameUnique = Rule::unique('eateries', 'name');
        $permitUnique = Rule::unique('permits', 'permit_no');

        if ($eatery instanceof Eatery) {
            $nameUnique->ignore($eatery->getKey());
            $permitId = $eatery->permit()->value('id');

            if ($permitId !== null) {
                $permitUnique->ignore($permitId);
            }
        }

        return [
            'name' => ['required', 'string', 'max:100', $nameUnique],
            'district_id' => ['required', 'integer', 'exists:districts,id'],
            'address' => ['required', 'string', 'max:255'],
            'price_range' => ['required', Rule::in(['budget', 'mid', 'splurge'])],
            'specialty' => ['nullable', 'string', 'max:100'],
            'is_open' => ['boolean'],
            'features' => ['required', 'array', 'min:1'],
            'features.*' => ['required', 'integer', 'exists:features,id'],
            'permit_no' => [
                'required',
                'regex:/^DVO-\d{4}-\d{4,5}$/',
                $permitUnique,
            ],
            'expires_on' => ['required', 'date', 'after:today'],
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
            'name.required' => 'Enter the eatery name.',
            'name.unique' => 'Kaon Ta Pastores already lists an eatery with this name.',
            'district_id.required' => 'Choose a district for the eatery.',
            'district_id.exists' => 'The selected district could not be found.',
            'address.required' => 'Enter the eatery address.',
            'price_range.required' => 'Choose a price range.',
            'price_range.in' => 'Choose budget, mid, or splurge for the price range.',
            'features.required' => 'Pick at least one feature.',
            'features.min' => 'Pick at least one feature.',
            'features.*.exists' => 'One of the selected features could not be found.',
            'permit_no.required' => 'Enter a permit number.',
            'permit_no.regex' => 'Permit number must look like DVO-2026-0415.',
            'expires_on.required' => 'Enter the permit expiry date.',
            'expires_on.after' => 'The permit must not be expired.',
        ];
    }
}
