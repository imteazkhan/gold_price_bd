<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedEstimate extends Model
{
    protected $fillable = [
        'visitor_id', 'purity_id', 'karat', 'weight_grams', 'rate_per_gram', 'making_percent', 'vat_percent',
        'base_amount', 'making_amount', 'vat_amount', 'total_amount', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'weight_grams' => 'decimal:3',
            'rate_per_gram' => 'decimal:2',
            'making_percent' => 'decimal:2',
            'vat_percent' => 'decimal:2',
            'base_amount' => 'decimal:2',
            'making_amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function purity(): BelongsTo
    {
        return $this->belongsTo(Purity::class);
    }
}
