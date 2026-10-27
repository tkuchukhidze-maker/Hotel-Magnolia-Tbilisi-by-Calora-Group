<?php

namespace App\Services;

use App\Models\MarketingCampaign;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MarketingAiService
{
    /** @return array<string, string> */
    public function generateCampaign(MarketingCampaign $campaign): array
    {
        $fields = [
            'strategy_ka', 'headline_ka', 'facebook_ka', 'instagram_ka',
            'headline_en', 'facebook_en', 'headline_ru', 'facebook_ru',
            'google_ads_en', 'seo_description_en', 'visual_brief_en',
        ];

        $schema = [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => array_fill_keys($fields, ['type' => 'string']),
            'required' => $fields,
        ];

        $instructions = implode("\n", [
            'You are a senior hotel marketing strategist and multilingual copywriter.',
            'Return exactly the requested JSON object; no markdown, no commentary.',
            'Use natural Georgian for _ka fields, English for _en, Russian for _ru.',
            'Create a practical campaign strategy, premium human copy, specific CTAs and a photography brief.',
            'Only use facts in the provided context. Never invent availability, guaranteed discounts,',
            'guest ratings, awards, occupancy, paid revenue, amenities, cancellation conditions or urgency.',
            'If an offer is not specified, do not add one. Avoid manipulative or unsupported claims.',
            'These are draft materials for human approval, never automatically publish anything.',
        ]);

        $json = $this->call($instructions, json_encode([
            'hotel' => $this->hotelFacts(),
            'campaign' => [
                'name' => $campaign->name,
                'goal' => $campaign->goal,
                'audience' => $campaign->audience,
                'market' => $campaign->market,
                'offer' => $campaign->offer,
                'channels' => $campaign->channels,
                'budget_gel' => $campaign->budget_gel,
                'starts_at' => $campaign->starts_at?->toDateString(),
                'ends_at' => $campaign->ends_at?->toDateString(),
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), [
            'type' => 'json_schema',
            'name' => 'hotel_marketing_assets',
            'strict' => true,
            'schema' => $schema,
        ]);

        $content = json_decode($json, true);

        if (! is_array($content)) {
            throw new RuntimeException('AI პასუხის დამუშავება ვერ მოხერხდა. სცადეთ ხელახლა.');
        }

        foreach ($fields as $field) {
            if (! isset($content[$field]) || ! is_string($content[$field])) {
                throw new RuntimeException('AI პასუხი არასრულია. სცადეთ ხელახლა.');
            }
        }

        return array_intersect_key($content, array_flip($fields));
    }

    public function answer(string $question, array $metrics): string
    {
        $instructions = implode("\n", [
            'You are the hotel marketing assistant for one specific boutique hotel in Tbilisi.',
            'Reply in natural Georgian, concisely, with actionable suggestions and next steps.',
            'Base conclusions only on the provided hotel facts and aggregate booking-request metrics.',
            'Booking requests are NOT paid or confirmed sales. Do not treat requested values as revenue.',
            'No channel attribution, ROAS, occupancy, competitor intelligence, or conversion data exists yet.',
            'If asked for unavailable data, clearly state what integration or measurement is missing.',
            'Never assume or invent promotions, prices, guest ratings or verified amenities.',
            'You can propose campaigns, copy, SEO and testing ideas, but cannot publish ads or change room prices.',
        ]);

        return $this->call($instructions, json_encode([
            'hotel' => $this->hotelFacts(),
            'aggregated_metrics' => $metrics,
            'question' => $question,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    private function hotelFacts(): array
    {
        return [
            'name' => config('hotel.name'),
            'address' => config('hotel.contact.address'),
            'city' => config('hotel.contact.city'),
            'website' => config('app.url'),
            'room_types' => collect(config('hotel.rooms'))->pluck('name')->values()->all(),
            'known_location' => 'Old Tbilisi, Georgia',
            'booking_type' => 'Website collects requests; the hotel confirms requests with guests.',
        ];
    }

    private function call(string $instructions, string $input, ?array $format = null): string
    {
        $key = trim((string) config('services.openai.key', ''));

        if ($key === '') {
            throw new RuntimeException('AI ჯერ არ არის ჩართული. Laravel Cloud-ის გარემოში დაამატეთ OPENAI_API_KEY.');
        }

        $payload = [
            'model' => config('services.openai.model', 'gpt-5-mini'),
            'store' => false,
            'instructions' => $instructions,
            'input' => $input,
            'max_output_tokens' => 3500,
        ];

        if ($format !== null) {
            $payload['text'] = ['format' => $format];
        }

        try {
            $response = Http::withToken($key)->acceptJson()->timeout(55)
                ->post('https://api.openai.com/v1/responses', $payload);
        } catch (\Throwable $e) {
            Log::warning('Hotel marketing AI connection failed', ['exception_type' => get_class($e)]);

            throw new RuntimeException('AI სერვერთან დაკავშირება ვერ მოხერხდა.');
        }

        if (! $response->successful()) {
            // Do not log response bodies: they may contain provider details.
            Log::warning('Hotel marketing AI returned an error', ['status' => $response->status()]);

            throw new RuntimeException('AI სერვისმა მოთხოვნა ვერ შეასრულა. შეამოწმეთ გასაღები და ლიმიტები.');
        }

        foreach (($response->json('output') ?? []) as $item) {
            foreach (($item['content'] ?? []) as $part) {
                if (($part['type'] ?? null) === 'output_text' && is_string($part['text'] ?? null)) {
                    return $part['text'];
                }
            }
        }

        throw new RuntimeException('AI სერვისიდან ტექსტური პასუხი ვერ მივიღეთ. სცადეთ ხელახლა.');
    }
}
