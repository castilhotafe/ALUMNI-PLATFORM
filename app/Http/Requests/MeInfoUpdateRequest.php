<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MeInfoUpdateRequest extends FormRequest
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
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string', 'max:1000'],
            'details' => ['nullable', 'array', 'max:20'],
            'details.*' => ['string', 'max:100'],
            'resume_link' => ['nullable', 'url'],
            'picture_url' => ['nullable', 'url'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'bio' => ['nullable', 'string', 'max:1000'],
            'resume_link' => ['nullable', 'url'],
            'picture_url' => ['nullable', 'url'],
            'details' => ['nullable', 'array'],
            'details.*' => ['string', 'max:100'],
        ];
    }
}
