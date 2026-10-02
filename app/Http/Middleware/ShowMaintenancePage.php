<?php

namespace App\Http\Middleware;

use App\Settings\SiteSettings;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * While maintenance mode is enabled in the control panel, visitors see a maintenance
 * page (503). Signed-in users keep browsing the website to review their changes.
 */
class ShowMaintenancePage
{
    public function __construct(private readonly SiteSettings $site) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->site->maintenance_mode && $request->user() === null) {
            return Inertia::render('Maintenance')
                ->toResponse($request)
                ->setStatusCode(Response::HTTP_SERVICE_UNAVAILABLE)
                ->header('Retry-After', '3600');
        }

        return $next($request);
    }
}
