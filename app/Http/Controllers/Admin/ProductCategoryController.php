<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveProductsRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ProductCategoryController extends Controller
{
    /**
     * Move several products to another category, e.g. before deleting their current one.
     */
    public function __invoke(MoveProductsRequest $request): RedirectResponse
    {
        $moved = Product::query()
            ->whereKey($request->validated('ids'))
            ->update(['category_id' => $request->integer('category_id')]);

        Inertia::flash('toast', ['type' => 'success', 'message' => trans_choice('{1} :count product moved.|[2,*] :count products moved.', $moved, ['count' => $moved])]);

        return back();
    }
}
