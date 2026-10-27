<?php

namespace App\Support;

/**
 * schema.org JSON-LD built from config/hotel.php, so the markup always
 * matches what the page shows. Placeholders like "[14:00]" are skipped.
 */
class StructuredData
{
    public static function hotel(): array
    {
        $c = config('hotel.contact');
        $rooms = collect(config('hotel.rooms'));
        $prices = $rooms->flatMap(fn ($r) => array_merge($r['p1'], $r['p2']));

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Hotel',
            '@id' => url('/').'#hotel',
            'name' => config('hotel.name'),
            'description' => 'A small boutique hotel in a restored brick house in Tbilisi\'s Old Town, with a rooftop terrace, free parking and a 127-year-old magnolia in the courtyard.',
            'url' => url('/'),
            'telephone' => $c['phone_tel'],
            'image' => [
                asset('assets/img/hero-facade.webp'),
                asset('assets/img/lobby.webp'),
                asset('assets/img/rooftop-view.webp'),
                asset('assets/img/magnolia-courtyard.webp'),
                asset('assets/img/room-deluxe.webp'),
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $c['address'],
                'addressLocality' => $c['city'],
                'addressCountry' => $c['country'],
            ],
            'hasMap' => $c['google_maps'],
            'priceRange' => 'GEL '.$prices->min().'–'.$prices->max(),
            'currenciesAccepted' => 'GEL',
            'amenityFeature' => collect([
                'Free parking', 'Free Wi-Fi', '24-hour front desk', 'Buffet breakfast',
                'Air conditioning', 'Rooftop terrace', 'Garden', 'Family rooms', 'Non-smoking rooms',
            ])->map(fn ($a) => ['@type' => 'LocationFeatureSpecification', 'name' => $a, 'value' => true])->all(),
            'availableLanguage' => ['en', 'ka', 'ru'],
        ];

        if (! self::isPlaceholder($c['email_display'])) {
            $data['email'] = $c['email'];
        }

        $sameAs = array_values(array_filter([$c['booking_com'], $c['instagram'], $c['facebook']]));
        if ($sameAs) {
            $data['sameAs'] = $sameAs;
        }

        foreach (['check_in' => 'checkinTime', 'check_out' => 'checkoutTime'] as $key => $prop) {
            $value = config('hotel.policies.'.$key);
            if (! self::isPlaceholder($value) && preg_match('/(\d{1,2}:\d{2})/', $value, $m)) {
                $data[$prop] = $m[1];
            }
        }

        return $data;
    }

    /** HotelRoom + Offer for every room type (Rooms page). */
    public static function rooms(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => collect(config('hotel.rooms'))->map(function ($r, $id) {
                $room = [
                    '@type' => 'HotelRoom',
                    '@id' => route('rooms').'#room-'.$id,
                    'name' => $r['name'],
                    'description' => $r['blurb'],
                    'containedInPlace' => ['@id' => url('/').'#hotel'],
                    'occupancy' => ['@type' => 'QuantitativeValue', 'maxValue' => $r['cap'], 'unitText' => 'guests'],
                    'bed' => $r['bed'],
                    'floorSize' => ['@type' => 'QuantitativeValue', 'value' => (int) $r['size'], 'unitCode' => 'MTK'],
                    'amenityFeature' => collect(['Air conditioning', 'Private bathroom', 'Flat-screen TV', 'Free Wi-Fi', $r['extra']])
                        ->map(fn ($a) => ['@type' => 'LocationFeatureSpecification', 'name' => $a, 'value' => true])->all(),
                ];
                if ($r['img']) {
                    $room['image'] = asset($r['img']);
                }

                return [
                    '@type' => 'Offer',
                    'name' => $r['name'],
                    'url' => route('rooms', ['room' => $id]),
                    'priceCurrency' => 'GEL',
                    'price' => $r['cap'] === 1 ? $r['p1'][0] : $r['p2'][0],
                    'priceSpecification' => [
                        '@type' => 'UnitPriceSpecification',
                        'price' => $r['cap'] === 1 ? $r['p1'][0] : $r['p2'][0],
                        'priceCurrency' => 'GEL',
                        'unitCode' => 'DAY',
                        'valueAddedTaxIncluded' => true,
                    ],
                    'availability' => 'https://schema.org/InStock',
                    'offeredBy' => ['@id' => url('/').'#hotel'],
                    'itemOffered' => $room,
                ];
            })->values()->all(),
        ];
    }

    public static function faq(array $faq): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faq)->map(fn ($q) => [
                '@type' => 'Question',
                'name' => $q[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $q[1]],
            ])->all(),
        ];
    }

    /** @param array<string,string> $trail label => url, home first */
    public static function breadcrumb(array $trail): array
    {
        $i = 0;

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($trail)->map(fn ($url, $label) => [
                '@type' => 'ListItem',
                'position' => ++$i,
                'name' => $label,
                'item' => $url,
            ])->values()->all(),
        ];
    }

    public static function render(array $data): string
    {
        return '<script type="application/ld+json">'
            .json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG)
            .'</script>';
    }

    public static function isPlaceholder(?string $value): bool
    {
        return $value === null || $value === '' || str_contains($value, '[');
    }
}
