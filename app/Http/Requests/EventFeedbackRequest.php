<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'      => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name'  => ['nullable', 'string', 'max:100'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'feedback'   => ['required', 'string', 'min:3', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required'   => 'Please select a star rating.',
            'rating.min'        => 'Rating must be at least 1 star.',
            'rating.max'        => 'Rating cannot exceed 5 stars.',
            'feedback.required' => 'Please share your feedback.',
            'feedback.min'      => 'Feedback must be at least 3 characters.',
            'feedback.max'      => 'Feedback cannot exceed 5000 characters.',
        ];
    }
}
