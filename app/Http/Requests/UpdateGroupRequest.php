<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $group = $this->route('group');

        // Check if the group exists and the current user is the creator
        return $group && (int) $group->creator_user_id === (int) $this->user()?->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'profile_bio' => ['nullable', 'string', 'max:1000'],
            'profile_details' => ['nullable', 'array', 'max:20'],
            'profile_details.*' => ['nullable', 'string', 'max:100'],
            'picture_url' => ['nullable', 'url', 'max:255'],
            'project_ids' => ['nullable', 'array'],
            'project_ids.*' => ['integer', 'exists:projects,id'],
            'paper_ids' => ['nullable', 'array'],
            'paper_ids.*' => ['integer', 'exists:research_papers,id'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ];
    }
}
