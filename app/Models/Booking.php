<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'reference', 'room', 'rate', 'checkin', 'checkout', 'nights',
        'adults', 'children', 'nightly_price', 'total',
        'first_name', 'last_name', 'email', 'phone', 'country',
        'address', 'city', 'postal_code',
        'arrival', 'notes', 'privacy_accepted_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'checkin' => 'date',
            'checkout' => 'date',
            'nights' => 'integer',
            'adults' => 'integer',
            'children' => 'integer',
            'nightly_price' => 'integer',
            'total' => 'integer',
            'privacy_accepted_at' => 'datetime',
        ];
    }

    public function guestName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function roomName(): string
    {
        return config('hotel.rooms.'.$this->room.'.name', $this->room);
    }

    public function rateName(): string
    {
        return $this->rate === 'bf' ? 'with breakfast' : 'room only';
    }
}
