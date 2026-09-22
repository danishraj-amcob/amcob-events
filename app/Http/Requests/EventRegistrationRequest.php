<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class EventRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $guestRules = [
            'email'         => ['required', 'email', 'max:255'],
            'first_name'    => ['required', 'string', 'max:255'],
            'last_name'     => ['required', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:50'],
            'job_title'     => ['nullable', 'string', 'max:255'],
            'company_name'  => ['nullable', 'string', 'max:255'],
            'linkedin_url'  => ['nullable', 'url', 'max:500'],
        ];

        $shared = [
            'ticket_id'       => ['required'],
            'coupon_code'     => ['nullable', 'string', 'max:64'],
            'card_number'     => ['nullable', 'digits_between:12,19'],
            'card_expiration' => ['nullable', 'regex:/^\d{2}\/?\d{2}$/'],
            'card_cvv'        => ['nullable', 'digits_between:3,4'],
        ];

        // Multi-guest: ticket_id + guests[{email, name, …}]
        if ($this->filled('guests') && is_array($this->input('guests'))) {
            $rules = array_merge($shared, [
                'guests'   => ['required', 'array', 'min:1', 'max:50'],
                'guests.*' => ['required', 'array'],
            ]);

            foreach ($guestRules as $field => $fieldRules) {
                $rules["guests.*.{$field}"] = $fieldRules;
            }

            return $rules;
        }

        // Single-guest (legacy flat fields)
        return array_merge($shared, $guestRules);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $guests = $this->input('guests');
            if (!is_array($guests)) {
                return;
            }

            $emails = [];
            foreach ($guests as $i => $guest) {
                $email = strtolower(trim((string) ($guest['email'] ?? '')));
                if ($email === '') {
                    continue;
                }
                if (isset($emails[$email])) {
                    $validator->errors()->add(
                        "guests.{$i}.email",
                        'Each guest must use a unique email address.'
                    );
                }
                $emails[$email] = true;
            }
        });
    }

    public function messages(): array
    {
        return [
            'guests.required'            => 'Add at least one guest to continue.',
            'guests.min'                 => 'Add at least one guest to continue.',
            'guests.*.email.required'    => 'Guest email is required.',
            'guests.*.email.email'       => 'Please enter a valid guest email.',
            'guests.*.first_name.required' => 'Guest first name is required.',
            'guests.*.last_name.required'  => 'Guest last name is required.',
            'guests.*.linkedin_url.url'  => 'Please enter a valid LinkedIn URL.',
        ];
    }
}
