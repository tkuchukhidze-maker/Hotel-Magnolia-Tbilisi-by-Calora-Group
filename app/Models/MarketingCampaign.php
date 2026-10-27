<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingCampaign extends Model
{
    protected $fillable = [
        'created_by', 'name', 'goal', 'audience', 'market', 'offer',
        'channels', 'budget_gel', 'starts_at', 'ends_at', 'status',
        'generated_content', 'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'budget_gel' => 'decimal:2',
            'starts_at' => 'date',
            'ends_at' => 'date',
            'generated_content' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
