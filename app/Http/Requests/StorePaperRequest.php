<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePaperRequest extends FormRequest
{
    /**
     * The key to be used for the view error bag.
     *
     * @var string
     */
    protected $errorBag = 'paper';

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
            'abstract' => ['required', 'string'],
            'doi' => ['nullable', 'string', 'max:255', 'unique:research_papers,doi'],
            'pdf_url' => ['nullable', 'url', 'max:255'],
            'visibility' => ['required', 'string', 'in:public,private'],
            'authors' => ['nullable', 'array'],
            'authors.*.name' => ['nullable', 'string', 'max:255'],
            'authors.*.order' => ['nullable', 'integer', 'min:1'],
            'profile_bio' => ['nullable', 'string', 'max:1000'],
            'profile_details' => ['nullable', 'array', 'max:20'],
            'profile_details.*' => ['nullable', 'string', 'max:100'],
            'picture_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
