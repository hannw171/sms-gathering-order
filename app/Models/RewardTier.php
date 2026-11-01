<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tier_level',
        'name',
        'min_spending',
        'reward_description',
        'badge_icon',
    ];

    protected $casts = [
        'tier_level' => 'integer',
        'min_spending' => 'decimal:2',
    ];
}
