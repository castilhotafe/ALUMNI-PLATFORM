<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to delete this project.
     */
    public function authorize(): bool
    {
        // Fetch the project instance from the route parameters
        $project = $this->route('project');

        // Check if the user is associated with this project before allowing deletion
        return $project && $project->users()->whereKey($this->user()->id)->exists();
    }

    /**
     * No validation rules are needed for a deletion request.
     */
    public function rules(): array
    {
        return [];
    }
}
