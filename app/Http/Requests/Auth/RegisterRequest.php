<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
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
        $requiresStudentId = in_array($this->input('role_selection'), ['current', 'alumni'], true);

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_selection' => [
                'required',
                'string',
                Rule::in(['current', 'alumni', 'lecturer', 'partner', 'general']),
            ],
            'student_id' => [
                'nullable',
                Rule::requiredIf($requiresStudentId),
                'digits:8',
            ],
        ];
    }

    /**
     * Custom user-friendly error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role_selection.required' => 'Please select your role within the community.',
            'role_selection.in' => 'Please select a valid role option.',
            'student_id.required_if' => 'A student ID is required for current students and alumni.',
            'student_id.digits' => 'The student ID must be exactly 8 digits.',
        ];
    }
}
