<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Stable public website URLs even while Laravel Cloud has a second hostname.
 */
final class PublicSite
{
    public const PUBLIC_ROUTES = [
        'home', 'rooms', 'offer', 'our-hotel', 'explore', 'contact', 'privacy',
        'sitemap', 'robots', 'llms',
    ];

    public static function baseUrl(): string
    {
        return rtrim((string) config('hotel.public_url', 'https://magnoliatbilisi.com'), '/');
    }

    public static function url(string $path = '/'): string
    {
        return self::baseUrl().'/'.ltrim($path, '/');
    }

    public static function route(string $name, array $parameters = []): string
    {
        return self::url(route($name, $parameters, false));
    }

    public static function canonical(): string
    {
        // Tracking query strings are intentionally omitted.
        return self::url(request()->getPathInfo());
    }

    public static function isPrimaryHost(?Request $request = null): bool
    {
        $request ??= request();
        $host = parse_url(self::baseUrl(), PHP_URL_HOST);

        return is_string($host) && strcasecmp($host, $request->getHost()) === 0;
    }

    public static function shouldIndex(): bool
    {
        return app()->environment('production')
            && self::isPrimaryHost()
            && ! request()->routeIs('privacy')
            && (! request()->routeIs('offer') || Offer::running());
    }

    /** Local images use the branded site; third-party image hosts stay untouched. */
    public static function image(?string $source): string
    {
        $source = trim((string) $source);
        if ($source === '') {
            return self::url('assets/img/hero-facade.webp');
        }
        if (str_starts_with($source, '//')) {
            return 'https:'.$source;
        }
        if (preg_match('~^https?://~i', $source)) {
            $host = parse_url($source, PHP_URL_HOST);
            $path = (string) parse_url($source, PHP_URL_PATH);
            $isAsset = str_starts_with($path, '/assets/') || str_starts_with($path, '/storage/');
            $isApplicationHost = is_string($host) && (
                strcasecmp($host, request()->getHost()) === 0 ||
                str_ends_with(strtolower($host), '.laravel.cloud')
            );

            return $isAsset && $isApplicationHost ? self::url($path) : $source;
        }

        return self::url($source);
    }
}
