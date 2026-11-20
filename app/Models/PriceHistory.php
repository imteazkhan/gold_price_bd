<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceHistory extends Model
{
    protected $fillable = ['purity_id', 'price_per_gram', 'sell_price_per_gram', 'recorded_on', 'note', 'source', 'updated_by'];

    protected function casts(): array
    {
        return [
            'price_per_gram' => 'decimal:2',
            'sell_price_per_gram' => 'decimal:2',
            'recorded_on' => 'date',
        ];
    }

    public function purity(): BelongsTo
    {
        return $this->belongsTo(Purity::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
