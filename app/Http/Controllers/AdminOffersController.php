<?php

namespace App\Http\Controllers;

use App\Mail\GuestOfferEmail;
use App\Support\GuestOffers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

/** Write an offer, preview it, send a test, then send it to past guests. */
class AdminOffersController extends Controller
{
    private function guard(Request $request): void
    {
        abort_unless($request->session()->get('magnolia_admin') === true, 403);
    }

    private function offer(Request $request): array
    {
        return $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'headline' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:3000'],
            'discount' => ['nullable', 'integer', 'min:1', 'max:90'],
            'code' => ['nullable', 'string', 'max:40'],
            'button_label' => ['required', 'string', 'max:60'],
            'button_url' => ['required', 'url:http,https', 'max:500'],
            'valid_until' => ['nullable', 'date'],
            'audience' => ['required', Rule::in(array_keys(GuestOffers::AUDIENCES))],
            'extra' => ['nullable', 'string', 'max:20000'],
        ]);
    }

    public function index(Request $request)
    {
        $this->guard($request);
        $form = array_merge(GuestOffers::defaults(), ['extra' => ''], $request->old());
        $counts = collect(GuestOffers::AUDIENCES)->map(fn ($l, $k) => GuestOffers::audience($k)->count());
        $campaigns = DB::table('offer_campaigns')->orderByDesc('id')->limit(20)->get();
        $unsubscribed = DB::table('email_unsubscribes')->count();

        return view('admin.offers', compact('form', 'counts', 'campaigns', 'unsubscribed'));
    }

    /** The email exactly as a guest will see it (opens in a new tab). */
    public function preview(Request $request)
    {
        $this->guard($request);
        $offer = $this->offer($request);

        return response((new GuestOfferEmail($offer, 'guest@example.com', 'Nino'))->render());
    }

    public function test(Request $request)
    {
        $this->guard($request);
        $offer = $this->offer($request);
        $to = $request->validate(['test_to' => ['required', 'email']])['test_to'];

        try {
            Mail::to($to)->send(new GuestOfferEmail($offer, $to, 'Nino'));
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->withErrors(['mail' => 'Test email failed: '.$e->getMessage()]);
        }

        return back()->withInput()->with('success', "Test offer sent to {$to}. Check the inbox, then send it to your guests.");
    }

    public function send(Request $request)
    {
        $this->guard($request);
        $offer = $this->offer($request);
        $request->validate(['confirm' => ['accepted']], ['confirm.accepted' => 'Tick the box to confirm sending to your guests.']);

        $people = GuestOffers::audience($offer['audience'], GuestOffers::parseList($offer['extra'] ?? ''));
        if ($people->isEmpty()) {
            return back()->withInput()->withErrors(['audience' => 'Nobody to send to: no guest emails for this choice (or they have unsubscribed).']);
        }

        $id = DB::table('offer_campaigns')->insertGetId([
            'subject' => $offer['subject'], 'headline' => $offer['headline'], 'body' => $offer['body'],
            'discount' => $offer['discount'] ?? null, 'code' => $offer['code'] ?? null,
            'button_label' => $offer['button_label'], 'button_url' => $offer['button_url'],
            'valid_until' => $offer['valid_until'] ?? null, 'audience' => $offer['audience'],
            'created_at' => now(), 'updated_at' => now(),
        ]);

        @set_time_limit(0);
        $sent = 0;
        $failed = 0;
        foreach ($people as $p) {
            $row = ['offer_campaign_id' => $id, 'email' => $p['email'], 'created_at' => now(), 'updated_at' => now()];
            try {
                Mail::to($p['email'])->send(new GuestOfferEmail($offer, $p['email'], $p['first_name']));
                $row['sent_at'] = now();
                $sent++;
            } catch (Throwable $e) {
                report($e);
                $row['error'] = mb_substr($e->getMessage(), 0, 250);
                $failed++;
            }
            DB::table('offer_recipients')->insertOrIgnore($row);
        }

        DB::table('offer_campaigns')->where('id', $id)->update([
            'sent_count' => $sent, 'failed_count' => $failed, 'sent_at' => now(), 'updated_at' => now(),
        ]);

        $msg = "Offer sent to {$sent} ".($sent === 1 ? 'guest' : 'guests').'.';
        if ($failed) {
            return redirect()->route('admin.offers.index')->with('success', $msg)
                ->withErrors(['mail' => "{$failed} could not be sent. See the list below."]);
        }

        return redirect()->route('admin.offers.index')->with('success', $msg);
    }

    /** Who received a campaign (and who failed). */
    public function show(Request $request, int $id)
    {
        $this->guard($request);
        $campaign = DB::table('offer_campaigns')->find($id);
        abort_unless($campaign, 404);
        $recipients = DB::table('offer_recipients')->where('offer_campaign_id', $id)->orderBy('email')->get();

        return view('admin.offer-campaign', compact('campaign', 'recipients'));
    }
}
