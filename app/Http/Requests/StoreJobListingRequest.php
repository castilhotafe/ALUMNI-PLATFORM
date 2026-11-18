<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobListingRequest extends FormRequest
{
    /**
     * Define the named error bag for the redirection.
     */
    protected $errorBag = 'job_listing';

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
            'title' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'apply_link' => ['nullable', 'url', 'max:255'],
            'job_profile_bio' => ['nullable', 'string', 'max:1000'],
            'job_profile_details' => ['nullable', 'array', 'max:20'],
            'job_profile_details.*' => ['nullable', 'string', 'max:100'],
            'job_picture_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
