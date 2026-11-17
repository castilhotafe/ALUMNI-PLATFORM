<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEnrolmentListingRequest extends FormRequest
{
    /**
     * Define the named error bag for the redirection.
     */
    protected $errorBag = 'enrolment_listing';

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
        return [
            'course_title' => ['required', 'string', 'max:255'],
            'tafe_provider' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'enrol_link' => ['nullable', 'url', 'max:255'],
            'enrolment_profile_bio' => ['nullable', 'string', 'max:1000'],
            'enrolment_profile_details' => ['nullable', 'array', 'max:20'],
            'enrolment_profile_details.*' => ['nullable', 'string', 'max:100'],
            'enrolment_picture_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
