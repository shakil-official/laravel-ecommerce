<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating and updating a product.
 *
 * Business rules enforced here:
 *  - the subcategory must belong to the selected category
 *  - the image is required when creating, optional when editing
 *  - new price may not be higher than the old price
 */
class ProductRequest extends FormRequest
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
        $isCreate = $this->isMethod('POST');
        $mimes = implode(',', config('shop.image.mimes'));
        $maxKb = (int) config('shop.image.max_kb');

        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'subcategory_id' => [
                'required',
                'integer',
                Rule::exists('subcategories', 'id')->where('category_id', $this->input('category_id')),
            ],
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'image' => [
                $isCreate ? 'required' : 'nullable',
                'image',
                'mimes:'.$mimes,
                'max:'.$maxKb,
            ],
            'old_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'new_price' => ['required', 'numeric', 'min:0.01', 'max:99999999.99', 'lte:old_price'],
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
            'subcategory_id.required' => 'Please choose a subcategory.',
            'subcategory_id.exists' => 'The selected subcategory does not belong to the selected category.',
            'image.required' => 'Please upload a product image.',
            'image.max' => 'The image may not be larger than '.config('shop.image.max_kb').' KB.',
            'new_price.lte' => 'The new price must be less than or equal to the old price.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'subcategory_id' => 'subcategory',
            'old_price' => 'old price',
            'new_price' => 'new price',
        ];
    }
}
