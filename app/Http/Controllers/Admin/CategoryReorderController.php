<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderCategoryRequest;
use App\Services\Catalog\CategoryTreeService;
use Illuminate\Http\RedirectResponse;

class CategoryReorderController extends Controller
{
    /**
     * Apply a complete new order to the children of one parent.
     */
    public function __invoke(ReorderCategoryRequest $request, CategoryTreeService $categoryTree): RedirectResponse
    {
        $categoryTree->reorder($request->parentId(), $request->orderedIds());

        return back();
    }
}
