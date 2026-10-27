<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    private function guard(Request $request): void
    {
        abort_unless($request->session()->get('magnolia_admin') === true, 403);
    }

    public function loginForm(Request $request)
    {
        if ($request->session()->get('magnolia_admin') === true) return redirect()->route('admin.dashboard');
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate(['username' => ['required', 'string', 'alpha_dash:ascii', 'max:64'], 'password' => ['required', 'string']]);
        $username = (string) config('hotel_admin.username', 'admin');
        $hash = (string) config('hotel_admin.password_hash', '');
        if ($username === '' || $hash === '' || !hash_equals($username, $data['username']) || !Hash::check($data['password'], $hash)) {
            return back()->withErrors(['username' => 'Invalid credentials or admin account not configured.'])->onlyInput('username');
        }
        $request->session()->regenerate();
        $request->session()->put('magnolia_admin', true);
        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function dashboard(Request $request)
    {
        $this->guard($request);
        $today = now()->toDateString();
        $active = Booking::query()->whereNotIn('status', ['cancelled','rejected']);
        $stats = [
            'new_requests' => Booking::where('status', 'requested')->count(),
            'arrivals' => (clone $active)->whereDate('checkin', $today)->count(),
            'guests_today' => (clone $active)->whereDate('checkin', $today)->get()->sum(fn ($b) => $b->adults + $b->children),
            'base_value' => (clone $active)->whereBetween('checkin', [now()->startOfMonth(), now()->endOfMonth()])->sum('total'),
        ];
        $bookings = Booking::orderByDesc('created_at')->limit(100)->get();
        $rooms = \App\Support\RoomCatalog::all();
        $overrides = DB::table('room_rates')->get()->keyBy('room_type');
        $promotions = DB::table('hotel_promotions')->orderByDesc('created_at')->get();
        return view('admin.dashboard', compact('stats','bookings','rooms','overrides','promotions'));
    }

    public function bookingStatus(Request $request, Booking $booking)
    {
        $this->guard($request);
        $data = $request->validate(['status' => ['required', Rule::in(['requested','confirmed','cancelled','rejected','checked_in','checked_out'])]]);
        $booking->update(['status' => $data['status']]);
        return back()->with('success', 'Booking status updated.');
    }

    public function roomRate(Request $request, string $room)
    {
        $this->guard($request);
        abort_unless(array_key_exists($room, config('hotel.rooms')), 404);
        $data = $request->validate([
            'one_room' => ['required','integer','min:1','max:100000'],
            'one_breakfast' => ['required','integer','min:1','max:100000'],
            'two_room' => ['required','integer','min:1','max:100000'],
            'two_breakfast' => ['required','integer','min:1','max:100000'],
        ]);
        DB::table('room_rates')->updateOrInsert(['room_type' => $room], array_merge($data, ['updated_at' => now()]));
        return back()->with('success', 'Room rates saved. They now apply to new booking quotes.');
    }

    public function promotion(Request $request)
    {
        $this->guard($request);
        $data = $request->validate([
            'name' => ['required','string','max:100'],
            'code' => ['required','alpha_dash','max:32','unique:hotel_promotions,code'],
            'discount_percent' => ['required','integer','min:1','max:90'],
            'starts_at' => ['required','date'],
            'ends_at' => ['required','date','after_or_equal:starts_at'],
        ]);
        DB::table('hotel_promotions')->insert(array_merge($data, ['is_active' => false,'created_at' => now(),'updated_at' => now()]));
        return back()->with('success', 'Promotion saved as inactive draft. Public checkout integration is not enabled.');
    }
}
