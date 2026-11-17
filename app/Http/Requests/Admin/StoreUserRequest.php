<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole('Admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $requiresStudentId = in_array($this->input('role_selection'), ['current', 'alumni'], true);

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'role_selection' => ['required', 'string', Rule::in(['current', 'alumni', 'lecturer', 'partner', 'general'])],
            'status' => ['required', 'string', Rule::in(['active', 'pending'])],
            'student_id' => [
                'nullable',
                Rule::requiredIf($requiresStudentId),
                'digits:8',
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
            'emailVerified' => ['nullable', 'boolean'],
            'isVerifiedCurrent' => ['nullable', 'boolean'],
            'isVerifiedAlumni' => ['nullable', 'boolean'],
            'isVerifiedLecturer' => ['nullable', 'boolean'],
            'isVerifiedPartner' => ['nullable', 'boolean'],
        ];
    }
}
