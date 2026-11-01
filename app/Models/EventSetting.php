<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_name',
        'session_end_at',
        'is_ordering_active',
        'is_leaderboard_visible',
        'doorprize_announcement',
        'running_ticker_text',
    ];

    protected $casts = [
        'session_end_at' => 'datetime',
        'is_ordering_active' => 'boolean',
        'is_leaderboard_visible' => 'boolean',
    ];

    public static function current(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'event_name' => 'SMS Gathering & Order Night 2026',
                'session_end_at' => now()->addHours(2),
                'is_ordering_active' => true,
                'is_leaderboard_visible' => true,
                'doorprize_announcement' => 'DOORPRIZE EXCLUSIVE: Grand Prize Emas Logam Mulia & Paket Liburan',
                'running_ticker_text' => '🔥 Flash Deal Sesi PO Gathering SMS Sedang Berlangsung! Kumpulkan Order Sebanyak-banyaknya untuk Memenangkan Grand Prize Most Spender Malam Ini!',
            ]
        );
    }
}
