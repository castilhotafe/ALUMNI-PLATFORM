<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyProjectRequest extends FormRequest
{
    protected $errorBag = 'project';

    /**
     * Determine if the user is authorized to delete this project.
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
     * No validation rules are needed for a deletion request.
     */
    public function rules(): array
    {
        return [];
    }
}
