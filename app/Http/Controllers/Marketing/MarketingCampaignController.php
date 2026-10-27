<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use App\Services\MarketingAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MarketingCampaignController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'goal' => ['required', Rule::in(['direct_bookings', 'awareness', 'repeat_guests'])],
            'audience' => ['required', 'string', 'max:160'],
            'market' => ['required', 'string', 'max:80'],
            'offer' => ['nullable', 'string', 'max:1200'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['required', 'distinct', Rule::in(['facebook', 'instagram', 'google_ads', 'seo', 'email'])],
            'budget_gel' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'starts_at' => ['nullable', 'date_format:Y-m-d'],
            'ends_at' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:starts_at'],
        ]);

        MarketingCampaign::create([
            ...$data,
            'created_by' => $request->user()->id,
            'status' => 'draft',
        ]);

        return redirect()->to(route('marketing.dashboard').'#campaigns')
            ->with('status', 'კამპანია შენახულია მონახაზად. ის ჯერ არსად გამოქვეყნებულა.');
    }

    public function generate(MarketingCampaign $campaign, MarketingAiService $ai): RedirectResponse
    {
        try {
            $content = $ai->generateCampaign($campaign);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['ai' => $e->getMessage()]);
        }

        $campaign->update([
            'generated_content' => $content,
            'generated_at' => now(),
            'status' => 'content_ready',
        ]);

        return redirect()->to(route('marketing.dashboard').'#campaigns')
            ->with('status', 'AI მასალები მომზადებულია. გამოქვეყნებამდე გადაამოწმეთ.');
    }
}
