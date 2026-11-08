<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating and updating a category.
 */
class CategoryRequest extends FormRequest
{
    /** Anyone may submit; access is controlled by routes / middleware. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('categories', 'name')->ignore($this->route('category')),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** Normalise input (trim / format) before the rules run. */
    protected function prepareForValidation(): void
    {
        if (is_string($this->name)) {
            $this->merge(['name' => trim($this->name)]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'A category with this name already exists.',
        ];
    }
}
