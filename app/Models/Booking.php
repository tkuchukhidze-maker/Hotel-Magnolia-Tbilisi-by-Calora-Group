<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'reference', 'room', 'rate', 'checkin', 'checkout', 'nights',
        'adults', 'children', 'nightly_price', 'total',
        'name', 'email', 'phone', 'arrival', 'notes', 'status',
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
        ];
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
