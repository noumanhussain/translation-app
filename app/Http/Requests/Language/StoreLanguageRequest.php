<?php

declare(strict_types=1);

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;

class StoreLanguageRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:10', 'unique:languages,code'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
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
            'code.required' => 'The language code is required.',
            'code.max' => 'The language code may not be greater than :max characters.',
            'code.unique' => 'This language code is already in use.',
            'name.required' => 'The language name is required.',
            'name.max' => 'The language name may not be greater than :max characters.',
            'is_active.boolean' => 'The active status must be true or false.',
        ];
    }
}
