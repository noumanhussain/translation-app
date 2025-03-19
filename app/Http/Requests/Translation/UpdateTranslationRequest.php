<?php

declare(strict_types=1);

namespace App\Http\Requests\Translation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTranslationRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'key' => ['sometimes', 'string'],
            'value' => ['sometimes', 'string'],
            'language_id' => ['sometimes', 'exists:languages,id'],
            'group' => ['sometimes', 'string', 'max:255'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['exists:tags,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key.string' => 'The translation key must be a string.',
            'value.string' => 'The translation value must be a string.',
            'language_id.exists' => 'The selected language is invalid.',
            'group.max' => 'The group name may not be greater than :max characters.',
            'tags.array' => 'Tags must be provided as an array.',
            'tags.*.exists' => 'One or more selected tags are invalid.',
        ];
    }
}
