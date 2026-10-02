<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the control panel dashboard with catalog and inbox figures.
     */
    public function __invoke(): Response
    {
        $products = Product::query()->toBase()
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when status = 1 then 0 else 1 end) as hidden')
            ->selectRaw('sum(case when featured = 1 then 1 else 0 end) as featured')
            ->first();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'categories' => Category::query()->count(),
                'products' => (int) $products->total,
                'hiddenProducts' => (int) $products->hidden,
                'featuredProducts' => (int) $products->featured,
                'unreadMessages' => ContactMessage::query()->unread()->count(),
            ],
        ]);
    }
}
