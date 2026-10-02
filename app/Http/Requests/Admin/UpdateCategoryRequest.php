<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->category());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        // Deeper cycles (a descendant as the new parent) are refused by CategoryTreeService::move().
        $rules['parent_id'][] = Rule::notIn([$this->category()->id]);

        return [
            ...$rules,
            'remove_image' => ['boolean'],
        ];
    }

    public function category(): Category
    {
        return $this->route('category');
    }

    protected function ignoredCategoryId(): ?int
    {
        return $this->category()->id;
    }
}
