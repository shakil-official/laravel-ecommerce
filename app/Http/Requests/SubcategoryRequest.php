<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating and updating a subcategory.
 */
class SubcategoryRequest extends FormRequest
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
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                // Unique only inside the chosen parent category.
                Rule::unique('subcategories', 'name')
                    ->where('category_id', $this->input('category_id'))
                    ->ignore($this->route('subcategory')),
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
            'category_id.required' => 'Please choose a parent category.',
            'category_id.exists' => 'The selected category does not exist.',
            'name.unique' => 'This category already has a subcategory with that name.',
        ];
    }
}
