<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaperRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $paper = $this->route('paper');

        return $paper && $paper->users()->whereKey($this->user()->id)->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $paperId = $this->route('paper')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string'],
            'doi' => ['nullable', 'string', 'max:255', Rule::unique('research_papers', 'doi')->ignore($paperId)],
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
