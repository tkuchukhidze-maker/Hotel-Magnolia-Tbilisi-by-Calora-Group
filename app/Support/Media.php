<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Photos the hotel can change in the admin panel.
 *
 * - Slots: one photo in a fixed place (a room's photo, the home page hero …).
 * - Gallery: the photo list on the Our Hotel page.
 *
 * Built-in photos live in public/assets/img; uploads go to the media disk
 * (HOTEL_MEDIA_DISK, e.g. a Laravel Cloud bucket). If the database is not
 * reachable the built-in photos are used, so the site never breaks.
 */
class Media
{
    /** Fixed photo places: key => [label, default path, default alt]. Room slots are added from config. */
    public const SLOTS = [
        'home.hero' => ['Home page · large top photo', 'assets/img/hero-facade.webp', 'Hotel Magnolia Tbilisi by Calora Group at golden hour: a red-brick facade with glowing windows, a wrought-iron balcony and two street trees'],
        'home.lobby' => ['Home page · "Meet Magnolia"', 'assets/img/lobby.webp', "Magnolia's lobby: gold chandeliers, red floral armchairs, blue tiles and a round wooden table"],
        'magnolia' => ['The magnolia tree (home, offer, our hotel)', 'assets/img/magnolia-courtyard.webp', 'The 127-year-old magnolia in the hotel courtyard, rising between brick walls and a spiral staircase'],
        'home.cta' => ['Home page · bottom "Come on in"', 'assets/img/welcome-tray.webp', 'Tea, oranges and apples on a small black table under a gold-framed painting'],
        'offer.hero' => ['Offer page · large top photo', 'assets/img/rooftop-view.webp', 'The view from our rooftop terrace over the old roofs of Tbilisi'],
        'our-hotel.banner' => ['Our Hotel · top photo', 'assets/img/lobby.webp', "Magnolia's lobby: gold chandeliers, red floral armchairs, blue tiles and a carved wooden door"],
        'our-hotel.door' => ['Our Hotel · front door', 'assets/img/entrance-door.webp', "Magnolia's carved wooden front door with wrought-iron grilles"],
        'our-hotel.stair' => ['Our Hotel · spiral stair', 'assets/img/magnolia-stair.webp', 'A magnolia tree growing around a black spiral staircase'],
        'explore.banner' => ['Explore Tbilisi · top photo', 'assets/img/rooftop-domes.webp', 'Glass domes on our roof with the old town of Tbilisi behind'],
        'contact.banner' => ['Contact · photo when the page is shared', 'assets/img/entrance-door.webp', "Magnolia's carved wooden front door"],
    ];

    public const CATEGORIES = ['rooms' => 'Rooms', 'details' => 'Details', 'spaces' => 'Spaces'];

    /** Built-in gallery, used until the database is available. */
    public const DEFAULT_GALLERY = [
        ['rooftop-view', 'spaces', true, 'Rooftop terrace with glass domes facing Mount Mtatsminda and the TV tower', 'Glass domes on the roof, Mtatsminda straight ahead.'],
        ['bathroom', 'details rooms', false, 'Bathroom with black-and-white patterned tiles', 'Patterned tiles, plenty of hot water.'],
        ['room-deluxe', 'rooms', false, 'Deluxe Room under two skylights', 'Wake up to sky, not street.'],
        ['room-double', 'rooms', false, 'Double Room with a skylight over a writing desk', 'A desk under the skylight.'],
        ['room-twin', 'details rooms', false, 'A gold-framed painting of haystacks above twin beds', 'Real paintings, in real gold frames.'],
        ['welcome-tray', 'details', false, 'Welcome tea and fruit on a small black table', 'Tea and fruit, waiting for you.'],
        ['lobby', 'spaces', false, 'The lobby with chandeliers and red armchairs', 'The lobby, for slow mornings.'],
        ['aerial', 'spaces', true, 'The hotel from above: old brick walls under a white louvred roof', 'Old brick below, a light new roof above.'],
        ['facade-street', 'spaces', true, 'The brick facade with tall wooden windows on a quiet street', 'A quiet street in the old town.'],
    ];

    public static function disk(): string
    {
        return (string) config('hotel.media_disk', 'public');
    }

    /** A usable URL for a stored path: built-in asset, full URL or a file on the media disk. */
    public static function src(?string $path): string
    {
        if (! $path) {
            return '';
        }
        if (Str::startsWith($path, ['http://', 'https://', '//', '/'])) {
            return $path;
        }
        if (Str::startsWith($path, 'assets/')) {
            return asset($path);
        }

        try {
            return Storage::disk(self::disk())->url($path);
        } catch (Throwable $e) {
            report($e);

            return '';
        }
    }

    /** All slot definitions including one per room type. */
    public static function slots(): array
    {
        $slots = [];
        foreach (config('hotel.rooms') as $id => $r) {
            $slots['room.'.$id] = [$r['name'].' · room photo', $r['img'] ?? null, $r['alt'] ?? $r['name']];
        }

        return $slots + self::SLOTS;
    }

    /** ['src' => url, 'alt' => text, 'path' => stored path, 'custom' => bool] for a slot. */
    public static function slot(string $key): array
    {
        $def = self::slots()[$key] ?? [null, null, ''];
        $row = self::rows()['slots'][$key] ?? null;
        $path = $row->path ?? $def[1];

        return [
            'src' => self::src($path),
            'alt' => ($row->alt ?? null) ?: $def[2],
            'path' => $path,
            'custom' => (bool) $row,
        ];
    }

    /** Visible gallery photos in order (falls back to the built-in list without a database). */
    public static function gallery(bool $all = false): array
    {
        $rows = self::rows();
        if ($rows['gallery'] === null) {
            return array_map(fn ($g) => (object) [
                'id' => null, 'path' => 'assets/img/'.$g[0].'.webp', 'category' => $g[1], 'wide' => $g[2],
                'alt' => $g[3], 'caption' => $g[4], 'is_visible' => true,
            ], self::DEFAULT_GALLERY);
        }

        return array_values(array_filter($rows['gallery'], fn ($p) => $all || $p->is_visible));
    }

    /** Clear the per-request cache (after admin changes). */
    public static function flush(): void
    {
        app()->forgetInstance('magnolia.media');
    }

    private static function rows(): array
    {
        if (app()->bound('magnolia.media')) {
            return app('magnolia.media');
        }
        $rows = ['slots' => [], 'gallery' => null];
        try {
            if (Schema::hasTable('site_photos')) {
                $all = DB::table('site_photos')->orderBy('sort')->orderBy('id')->get();
                $rows = [
                    'slots' => $all->whereNotNull('slot')->keyBy('slot')->all(),
                    'gallery' => $all->whereNull('slot')->values()->all(),
                ];
            }
        } catch (Throwable $e) {
            report($e);
        }
        app()->instance('magnolia.media', $rows);

        return $rows;
    }

    /**
     * Save an uploaded photo on the media disk. Large photos are scaled to at most
     * 2400 px and saved as WebP when the server can (GD); otherwise kept as uploaded.
     */
    public static function store(UploadedFile $file): string
    {
        $dir = 'magnolia/'.now()->format('Y/m');
        $name = Str::uuid()->toString();
        $disk = Storage::disk(self::disk());

        $webp = self::toWebp($file->getRealPath(), 2400);
        if ($webp !== null) {
            $path = $dir.'/'.$name.'.webp';
            $disk->put($path, $webp, ['visibility' => 'public', 'ContentType' => 'image/webp']);

            return $path;
        }

        return $disk->putFileAs($dir, $file, $name.'.'.strtolower($file->getClientOriginalExtension() ?: 'jpg'), ['visibility' => 'public']);
    }

    /** Remove a file we uploaded (never a built-in asset or an outside URL). */
    public static function forget(?string $path): void
    {
        if (! $path || Str::startsWith($path, ['assets/', 'http://', 'https://', '/'])) {
            return;
        }
        try {
            Storage::disk(self::disk())->delete($path);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private static function toWebp(string $source, int $max): ?string
    {
        if (! function_exists('imagewebp') || ! function_exists('imagecreatefromstring')) {
            return null;
        }
        $data = @file_get_contents($source);
        $img = $data !== false ? @imagecreatefromstring($data) : false;
        if (! $img) {
            return null;
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1, $max / max($w, $h));
        if ($scale < 1) {
            $nw = (int) round($w * $scale);
            $nh = (int) round($h * $scale);
            $resized = imagecreatetruecolor($nw, $nh);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($img);
            $img = $resized;
        }
        ob_start();
        $ok = imagewebp($img, null, 82);
        $out = ob_get_clean();
        imagedestroy($img);

        return $ok && $out ? $out : null;
    }
}
