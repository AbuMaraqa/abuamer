<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\Slide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SlideReorderController extends Controller
{
    /**
     * Save the order of the slides after a drag and drop.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        Gate::authorize(Permission::CompanyManage->value);

        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'distinct', Rule::exists('slides', 'id')],
        ]);

        DB::transaction(function () use ($validated) {
            foreach (array_values($validated['ids']) as $index => $id) {
                Slide::query()->whereKey($id)->update(['sort_order' => $index + 1]);
            }
        });

        return back();
    }
}
