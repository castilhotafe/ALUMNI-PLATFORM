<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Fetch the active project instance from the route parameters
        $project = $this->route('project');

        // Verify the user is linked to the project. Returns a 403 error automatically if false.
        return $project && $project->users()->whereKey($this->user()->id)->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'repo_url' => ['nullable', 'url', 'max:255'],
            'visibility' => ['required', 'string', 'in:public,private'],
            'collaborators' => ['nullable', 'array'],
            'collaborators.*.name' => ['nullable', 'string', 'max:255'],
            'collaborators.*.role' => ['nullable', 'string', 'max:255'],
            'profile_bio' => ['nullable', 'string', 'max:1000'],
            'profile_details' => ['nullable', 'array', 'max:20'],
            'profile_details.*' => ['nullable', 'string', 'max:100'],
            'picture_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
