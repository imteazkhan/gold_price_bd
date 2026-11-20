<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purity extends Model
{
    protected $fillable = [
        'metal', 'karat', 'label', 'purity_percent', 'tagline', 'description',
        'is_featured', 'show_in_table', 'show_in_calculator', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purity_percent' => 'decimal:2',
            'is_featured' => 'boolean',
            'show_in_table' => 'boolean',
            'show_in_calculator' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function histories(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByRaw("FIELD(metal, 'gold', 'silver')")->orderBy('sort_order')->orderBy('id');
    }
}
