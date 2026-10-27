<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MarketingCampaign;
use App\Services\MarketingAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketingDashboardController extends Controller
{
    public function index(): View
    {
        $from = now()->subDays(29)->startOfDay();
        $valid = fn () => Booking::query()->whereNotIn('status', Booking::CLOSED);
        $period = $valid()->where('created_at', '>=', $from);

        $stats = [
            'requests_30d' => (clone $period)->count(),
            // This is the value of booking requests, NOT recognized or paid revenue.
            'requested_value_30d' => (clone $period)->sum('total'),
            'arrivals_7d' => $valid()->whereDate('checkin', '>=', today())
                ->whereDate('checkin', '<=', today()->addDays(6))->count(),
            'campaigns' => MarketingCampaign::count(),
        ];

        $trend = collect(range(6, 0))->map(function (int $daysAgo) use ($valid) {
            $date = today()->subDays($daysAgo);

            return [
                'label' => $date->format('d M'),
                'count' => $valid()->whereDate('created_at', $date)->count(),
            ];
        });

        return view('marketing.dashboard', [
            'stats' => $stats,
            'trend' => $trend,
            'trendMax' => max(1, $trend->max('count')),
            'campaigns' => MarketingCampaign::latest()->limit(15)->get(),
            'aiConfigured' => filled(config('services.openai.key')),
            'hotel' => config('hotel'),
        ]);
    }

    public function ask(Request $request, MarketingAiService $ai): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'min:8', 'max:1500'],
        ]);

        $metrics = [
            'booking_requests_last_30_days' => Booking::whereNotIn('status', Booking::CLOSED)
                ->where('created_at', '>=', now()->subDays(29)->startOfDay())->count(),
            'total_value_of_requests_gel' => Booking::whereNotIn('status', Booking::CLOSED)
                ->where('created_at', '>=', now()->subDays(29)->startOfDay())->sum('total'),
        ];

        try {
            $answer = $ai->answer($data['question'], $metrics);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['ai' => $e->getMessage()])->withInput();
        }

        return back()->with('assistant_question', $data['question'])
            ->with('assistant_answer', $answer);
    }
}
