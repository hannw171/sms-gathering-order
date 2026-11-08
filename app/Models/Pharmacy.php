<?php

namespace App\Models;

use App\Services\GatheringService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pharmacy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'customer_code',
        'owner_name',
        'phone',
        'city',
        'sales_pic',
        'device_session_token',
        'device_name',
        'last_active_at',
        'is_checked_in',
        'checked_in_at',
        'reward_handed_over_at',
        'reward_receiver_name',
        'reward_handover_notes',
    ];

    protected $casts = [
        'is_checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
        'last_active_at' => 'datetime',
        'reward_handed_over_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            app(GatheringService::class)->flushLeaderboardCache();
        });

        static::deleted(function () {
            app(GatheringService::class)->flushLeaderboardCache();
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getTotalSpendingAttribute(): float
    {
        return (float) $this->orders()
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
    }
}
