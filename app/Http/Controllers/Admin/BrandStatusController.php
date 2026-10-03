<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BrandStatusController extends Controller
{
    /**
     * Show or hide a brand on the website.
     */
    public function __invoke(Request $request, Brand $brand): RedirectResponse
    {
        Gate::authorize(Permission::BrandsManage->value);

        $brand->update($request->validate([
            'status' => ['required', 'boolean'],
        ]));

        return back();
    }
}
