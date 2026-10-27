<?php

namespace App\Http\Controllers;

use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/** Photos page: room photos, fixed page photos and the Our Hotel gallery. */
class AdminMediaController extends Controller
{
    private const IMAGE = ['image', 'mimes:jpg,jpeg,png,webp', 'max:15360'];

    private function guard(Request $request): void
    {
        abort_unless($request->session()->get('magnolia_admin') === true, 403);
        abort_unless(Schema::hasTable('site_photos'), 503, 'Photos need the database migration. Run php artisan migrate --force, then reload.');
    }

    public function index(Request $request)
    {
        $this->guard($request);
        $slots = collect(Media::slots())->map(fn ($def, $key) => ['key' => $key, 'label' => $def[0]] + Media::slot($key))->values();

        return view('admin.media', [
            'roomSlots' => $slots->filter(fn ($s) => str_starts_with($s['key'], 'room.'))->values(),
            'pageSlots' => $slots->reject(fn ($s) => str_starts_with($s['key'], 'room.'))->values(),
            'gallery' => Media::gallery(all: true),
            'disk' => Media::disk(),
        ]);
    }

    /** Replace a fixed photo and/or its description. */
    public function saveSlot(Request $request, string $slot)
    {
        $this->guard($request);
        abort_unless(array_key_exists($slot, Media::slots()), 404);
        $data = $request->validate([
            'photo' => ['nullable', ...self::IMAGE],
            'alt' => ['nullable', 'string', 'max:255'],
        ], ['photo.max' => 'That photo is too large (15 MB max).', 'photo.image' => 'Please choose a photo (JPG, PNG or WebP).']);

        $row = DB::table('site_photos')->where('slot', $slot)->first();
        $path = $row->path ?? Media::slot($slot)['path'];
        if ($request->hasFile('photo')) {
            $new = Media::store($request->file('photo'));
            if ($row) {
                Media::forget($row->path);
            }
            $path = $new;
        }
        DB::table('site_photos')->updateOrInsert(['slot' => $slot], [
            'path' => $path,
            'alt' => $data['alt'] ?? ($row->alt ?? null),
            'updated_at' => now(),
            'created_at' => $row->created_at ?? now(),
        ]);
        Media::flush();

        return back()->with('success', 'Photo saved: '.Media::slots()[$slot][0].'.');
    }

    /** Go back to the original photo. */
    public function resetSlot(Request $request, string $slot)
    {
        $this->guard($request);
        $row = DB::table('site_photos')->where('slot', $slot)->first();
        if ($row) {
            Media::forget($row->path);
            DB::table('site_photos')->where('id', $row->id)->delete();
        }
        Media::flush();

        return back()->with('success', 'Original photo restored.');
    }

    /** Add one or more photos to the gallery. */
    public function addGallery(Request $request)
    {
        $this->guard($request);
        $request->validate([
            'photos' => ['required', 'array', 'min:1', 'max:30'],
            'photos.*' => self::IMAGE,
            'category' => ['nullable', Rule::in(array_keys(Media::CATEGORIES))],
        ], ['photos.required' => 'Choose at least one photo.', 'photos.*.max' => 'One of the photos is too large (15 MB max).']);

        $sort = (int) DB::table('site_photos')->whereNull('slot')->max('sort');
        foreach ($request->file('photos') as $file) {
            DB::table('site_photos')->insert([
                'path' => Media::store($file),
                'alt' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'category' => $request->input('category', 'spaces'),
                'is_visible' => true,
                'sort' => ++$sort,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $n = count($request->file('photos'));

        Media::flush();

        return back()->with('success', $n.' '.($n === 1 ? 'photo' : 'photos').' added to the gallery. Add a short description to each so everyone (and Google) knows what it shows.');
    }

    public function updateGallery(Request $request, int $id)
    {
        $this->guard($request);
        $row = DB::table('site_photos')->whereNull('slot')->where('id', $id)->first();
        abort_unless($row, 404);
        $data = $request->validate([
            'alt' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:160'],
            'categories' => ['nullable', 'array'],
            'categories.*' => [Rule::in(array_keys(Media::CATEGORIES))],
            'wide' => ['sometimes', 'boolean'],
            'is_visible' => ['sometimes', 'boolean'],
            'photo' => ['nullable', ...self::IMAGE],
        ]);
        $update = [
            'alt' => $data['alt'] ?? null,
            'caption' => $data['caption'] ?? null,
            'category' => implode(' ', $data['categories'] ?? ['spaces']),
            'wide' => $request->boolean('wide'),
            'is_visible' => $request->boolean('is_visible'),
            'updated_at' => now(),
        ];
        if ($request->hasFile('photo')) {
            $update['path'] = Media::store($request->file('photo'));
            Media::forget($row->path);
        }
        DB::table('site_photos')->where('id', $id)->update($update);
        Media::flush();

        return back()->with('success', 'Gallery photo saved.');
    }

    public function moveGallery(Request $request, int $id)
    {
        $this->guard($request);
        $dir = $request->validate(['dir' => ['required', Rule::in(['up', 'down', 'first', 'last'])]])['dir'];
        $ids = DB::table('site_photos')->whereNull('slot')->orderBy('sort')->orderBy('id')->pluck('id')->all();
        $i = array_search($id, $ids, true);
        abort_if($i === false, 404);
        array_splice($ids, $i, 1);
        $to = match ($dir) { 'up' => max(0, $i - 1), 'down' => min(count($ids), $i + 1), 'first' => 0, 'last' => count($ids) };
        array_splice($ids, $to, 0, [$id]);
        DB::transaction(function () use ($ids) {
            foreach ($ids as $pos => $pid) {
                DB::table('site_photos')->where('id', $pid)->update(['sort' => $pos + 1]);
            }
        });

        Media::flush();

        return back()->with('success', 'Order saved.');
    }

    public function deleteGallery(Request $request, int $id)
    {
        $this->guard($request);
        $row = DB::table('site_photos')->whereNull('slot')->where('id', $id)->first();
        abort_unless($row, 404);
        Media::forget($row->path);
        DB::table('site_photos')->where('id', $id)->delete();
        Media::flush();

        return back()->with('success', 'Photo removed from the gallery.');
    }
}
