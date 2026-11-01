<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'pharmacy_id',
        'total_amount',
        'total_items',
        'unlocked_reward_tier_id',
        'status',
        'notes',
        'ip_address',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_items' => 'integer',
    ];

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function unlockedRewardTier(): BelongsTo
    {
        return $this->belongsTo(RewardTier::class, 'unlocked_reward_tier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
