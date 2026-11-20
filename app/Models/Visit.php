<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'visitor_id', 'session_id', 'method', 'path', 'full_url', 'referrer',
        'ip_address', 'user_agent', 'device_type', 'visited_at',
    ];

    protected function casts(): array
    {
        return ['visited_at' => 'datetime'];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
}
