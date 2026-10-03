<?php

namespace App\Http\Requests\Admin;

use App\Models\Brand;

class UpdateBrandRequest extends StoreBrandRequest
{
    public function brand(): Brand
    {
        return $this->route('brand');
    }

    protected function ignoredBrandId(): ?int
    {
        return $this->brand()->id;
    }
}
