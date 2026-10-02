<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveCategoryRequest;
use App\Models\Category;
use App\Services\Catalog\CategoryTreeService;
use Illuminate\Http\RedirectResponse;

class CategoryMoveController extends Controller
{
    /**
     * Move a category (with its subtree) to a parent and position, e.g. after a drag and drop.
     */
    public function __invoke(MoveCategoryRequest $request, Category $category, CategoryTreeService $categoryTree): RedirectResponse
    {
        $categoryTree->move($category, $request->parentId(), $request->integer('sort_order'));

        return back();
    }
}
