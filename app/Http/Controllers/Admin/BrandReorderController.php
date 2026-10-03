<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BrandReorderController extends Controller
{
    /**
     * Save the order of the brands after a drag and drop.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        Gate::authorize(Permission::BrandsManage->value);

        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'distinct', Rule::exists('brands', 'id')],
        ]);

        DB::transaction(function () use ($validated) {
            foreach (array_values($validated['ids']) as $index => $id) {
                Brand::query()->whereKey($id)->update(['sort_order' => $index + 1]);
            }
        });

        return back();
    }
}
