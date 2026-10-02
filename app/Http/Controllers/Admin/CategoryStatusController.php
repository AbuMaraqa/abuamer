<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryStatusController extends Controller
{
    /**
     * Enable or disable a category. A disabled category hides its whole subtree on the website.
     */
    public function __invoke(Request $request, Category $category): RedirectResponse
    {
        Gate::authorize('update', $category);

        $category->update($request->validate([
            'status' => ['required', 'boolean'],
        ]));

        return back();
    }
}
