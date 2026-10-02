<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;

class UpdateProductRequest extends StoreProductRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->product());
    }

    public function product(): Product
    {
        return $this->route('product');
    }

    protected function ignoredProductId(): ?int
    {
        return $this->product()->id;
    }
}
