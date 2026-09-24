<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    protected $errorBag = 'project';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $project = $this->route('project');

        return $user !== null
            && $project !== null
            && $project->users()->whereKey($user->getKey())->exists();
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
            'description' => ['required', 'string'],
            'repo_url' => ['nullable', 'url', 'max:255'],
            'visibility' => ['required', 'string', 'in:public,private'],
            'collaborators' => ['nullable', 'array'],
            'collaborators.*' => ['array:name,role'],
            'collaborators.*.name' => ['nullable', 'string', 'max:255'],
            'collaborators.*.role' => ['nullable', 'string', 'max:255'],
            'profile_bio' => ['nullable', 'string', 'max:1000'],
            'profile_details' => ['nullable', 'array', 'max:20'],
            'profile_details.*' => ['nullable', 'string', 'max:100'],
            'picture_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
