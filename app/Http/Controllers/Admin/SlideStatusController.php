<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\Slide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SlideStatusController extends Controller
{
    /**
     * Show or hide a slide on the home page.
     */
    public function __invoke(Request $request, Slide $slide): RedirectResponse
    {
        Gate::authorize(Permission::CompanyManage->value);

        $slide->update($request->validate([
            'status' => ['required', 'boolean'],
        ]));

        return back();
    }
}
