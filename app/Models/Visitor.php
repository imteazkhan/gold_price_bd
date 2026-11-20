<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    protected $fillable = [
        'visitor_key', 'ip_address', 'user_agent', 'browser', 'platform', 'device_type', 'is_bot',
        'language', 'first_referrer', 'first_landing', 'total_visits', 'first_visit_at', 'last_visit_at',
    ];

    protected function casts(): array
    {
        return [
            'is_bot' => 'boolean',
            'total_visits' => 'integer',
            'first_visit_at' => 'datetime',
            'last_visit_at' => 'datetime',
        ];
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(SavedEstimate::class);
    }
}
