<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'reference', 'room', 'items', 'rooms_count', 'rate', 'checkin', 'checkout', 'nights',
        'adults', 'children', 'children_ages', 'nightly_price', 'total',
        'first_name', 'last_name', 'email', 'phone', 'country',
        'address', 'city', 'postal_code',
        'arrival', 'notes', 'privacy_accepted_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'children_ages' => 'array',
            'checkin' => 'date',
            'checkout' => 'date',
            'nights' => 'integer',
            'rooms_count' => 'integer',
            'adults' => 'integer',
            'children' => 'integer',
            'nightly_price' => 'integer',
            'total' => 'integer',
            'privacy_accepted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Keep the legacy `room` column filled with the first room type.
        static::saving(function (Booking $b) {
            if (! $b->room && ! empty($b->items)) {
                $b->room = $b->items[0]['room'];
            }
        });
    }

    public function guestName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /** e.g. "2 × Double Room, 1 × Triple Room" */
    public function roomsSummary(): string
    {
        return collect($this->items ?? [])
            ->map(fn ($l) => $l['qty'].' × '.$l['name'])
            ->implode(', ');
    }

    public function rateName(): string
    {
        return \App\Support\RoomPricing::plansAsNames()[$this->rate] ?? $this->rate;
    }
}
