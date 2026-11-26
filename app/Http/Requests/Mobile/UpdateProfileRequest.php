<?php

namespace App\Http\Requests\Mobile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $fields = array_keys($this->all());
            if (! array_intersect($fields, ['name', 'bio'])) {
                $validator->errors()->add('profile', 'Provide a name or biography.');
            }
            foreach (array_diff($fields, ['name', 'bio']) as $field) {
                $validator->errors()->add($field, 'Only name and biography can be edited.');
            }
        }];
    }
}
