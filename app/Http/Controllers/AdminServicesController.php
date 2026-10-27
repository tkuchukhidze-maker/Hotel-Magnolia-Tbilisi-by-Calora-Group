<?php

namespace App\Http\Controllers;

use App\Support\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Services page: what's included (amenities) and paid extras guests can add when booking. */
class AdminServicesController extends Controller
{
    private function guard(Request $request): void
    {
        abort_unless($request->session()->get('magnolia_admin') === true, 403);
        abort_unless(Schema::hasTable('hotel_services'), 503, 'Services need the database migration. Run php artisan migrate --force, then reload.');
    }

    public function index(Request $request)
    {
        $this->guard($request);
        $all = DB::table('hotel_services')->orderBy('sort')->orderBy('id')->get();

        return view('admin.services', [
            'amenities' => $all->where('kind', 'amenity')->values(),
            'extras' => $all->where('kind', 'extra')->values(),
            'units' => Services::UNITS,
            'icons' => Services::ICONS,
        ]);
    }

    private function rules(): array
    {
        return [
            'kind' => ['required', Rule::in(['amenity', 'extra'])],
            'name' => ['required', 'string', 'max:90'],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['required', Rule::in(array_keys(Services::ICONS))],
            'price' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'price_unit' => ['nullable', Rule::in(array_keys(Services::UNITS))],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private function clean(Request $request): array
    {
        $data = $request->validate($this->rules(), ['name.required' => 'Give the service a name.']);
        if ($data['kind'] === 'extra' && (! isset($data['price']) || empty($data['price_unit']))) {
            throw ValidationException::withMessages(['price' => 'A paid extra needs a price and how it is charged (per booking, per night …).']);
        }
        if ($data['kind'] === 'amenity') {
            $data['price'] = null;
            $data['price_unit'] = null;
        }
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    public function store(Request $request)
    {
        $this->guard($request);
        $data = $this->clean($request);
        $data['sort'] = (int) DB::table('hotel_services')->where('kind', $data['kind'])->max('sort') + 1;
        DB::table('hotel_services')->insert($data + ['created_at' => now(), 'updated_at' => now()]);

        return redirect()->route('admin.services.index')->withFragment($data['kind'] === 'extra' ? 'extras' : 'amenities')
            ->with('success', '"'.$data['name'].'" added'.($data['is_active'] ? ' and shown on the site.' : ' (hidden for now).'));
    }

    public function update(Request $request, int $id)
    {
        $this->guard($request);
        abort_unless(DB::table('hotel_services')->where('id', $id)->exists(), 404);
        $data = $this->clean($request);
        DB::table('hotel_services')->where('id', $id)->update($data + ['updated_at' => now()]);

        return back()->with('success', '"'.$data['name'].'" saved.');
    }

    public function toggle(Request $request, int $id)
    {
        $this->guard($request);
        $row = DB::table('hotel_services')->where('id', $id)->first();
        abort_unless($row, 404);
        DB::table('hotel_services')->where('id', $id)->update(['is_active' => ! $row->is_active, 'updated_at' => now()]);

        return back()->with('success', '"'.$row->name.'" is now '.($row->is_active ? 'hidden.' : 'shown on the site.'));
    }

    public function move(Request $request, int $id)
    {
        $this->guard($request);
        $dir = $request->validate(['dir' => ['required', Rule::in(['up', 'down'])]])['dir'];
        $row = DB::table('hotel_services')->where('id', $id)->first();
        abort_unless($row, 404);
        $ids = DB::table('hotel_services')->where('kind', $row->kind)->orderBy('sort')->orderBy('id')->pluck('id')->all();
        $i = array_search($id, $ids, true);
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if ($j >= 0 && $j < count($ids)) {
            [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
            DB::transaction(function () use ($ids) {
                foreach ($ids as $pos => $sid) {
                    DB::table('hotel_services')->where('id', $sid)->update(['sort' => $pos + 1]);
                }
            });
        }

        return back();
    }

    public function destroy(Request $request, int $id)
    {
        $this->guard($request);
        $row = DB::table('hotel_services')->where('id', $id)->first();
        abort_unless($row, 404);
        DB::table('hotel_services')->where('id', $id)->delete();

        return back()->with('success', '"'.$row->name.'" deleted. Bookings that already include it keep it.');
    }
}
