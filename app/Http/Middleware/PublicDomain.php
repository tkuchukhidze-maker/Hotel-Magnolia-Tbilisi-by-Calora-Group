<?php

namespace App\Http\Middleware;

use App\Support\Offer;
use App\Support\PublicSite;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SEO domain safety: do not expose the Laravel Cloud mirror as an indexable
 * website and never redirect visitors to DNS that has not been verified.
 */
final class PublicDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();
        $public = in_array($routeName, PublicSite::PUBLIC_ROUTES, true);

        if (
            $public
            && app()->environment('production')
            && config('hotel.custom_domain_ready')
            && ! PublicSite::isPrimaryHost($request)
            && in_array($request->method(), ['GET', 'HEAD'], true)
        ) {
            $query = $request->getQueryString();
            $destination = PublicSite::url($request->getPathInfo())
                .($query === null ? '' : '?'.$query);

            return redirect()->away($destination, 301);
        }

        $response = $next($request);
        if (
            ! app()->environment('production')
            || ! PublicSite::isPrimaryHost($request)
            || $request->is('admin', 'admin/*', 'booking-prices', 'unsubscribe/*')
            || $request->routeIs('privacy')
            || ($request->routeIs('offer') && ! Offer::running())
        ) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
