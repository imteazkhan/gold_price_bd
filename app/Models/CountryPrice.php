<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryPrice extends Model
{
    protected $fillable = ['country', 'flag', 'currency', 'karat', 'local_price', 'fx_to_bdt', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'local_price' => 'decimal:2',
            'fx_to_bdt' => 'decimal:4',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getBdtPerGramAttribute(): float
    {
        return round((float) $this->local_price * (float) $this->fx_to_bdt, 2);
    }
}
