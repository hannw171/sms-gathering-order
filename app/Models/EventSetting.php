<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSetting extends Model
{
    use HasFactory;

    protected static ?self $currentInstance = null;

    protected static function booted(): void
    {
        static::saved(function () {
            static::$currentInstance = null;
        });

        static::deleted(function () {
            static::$currentInstance = null;
        });
    }

    protected $fillable = [
        'event_name',
        'venue_name',
        'registration_schedule',
        'registration_help_note',
        'session_start_at',
        'session_end_at',
        'is_ordering_active',
        'is_leaderboard_visible',
        'doorprize_announcement',
        'running_ticker_text',
    ];

    protected $casts = [
        'session_start_at' => 'datetime',
        'session_end_at' => 'datetime',
        'is_ordering_active' => 'boolean',
        'is_leaderboard_visible' => 'boolean',
    ];

    public function isOrderingOpen(): bool
    {
        if (! $this->is_ordering_active) {
            return false;
        }

        $now = now();

        if ($this->session_start_at && $now->lt($this->session_start_at)) {
            return false;
        }

        if ($this->session_end_at && $now->gt($this->session_end_at)) {
            return false;
        }

        return true;
    }

    public function getOrderingStatus(): string
    {
        if (! $this->is_ordering_active) {
            return 'closed';
        }

        $now = now();

        if ($this->session_start_at && $now->lt($this->session_start_at)) {
            return 'scheduled';
        }

        if ($this->session_end_at && $now->gt($this->session_end_at)) {
            return 'ended';
        }

        return 'open';
    }

    public static function current(): self
    {
        if (static::$currentInstance !== null) {
            return static::$currentInstance;
        }

        return static::$currentInstance = self::firstOrCreate(
            ['id' => 1],
            [
                'event_name' => 'SMS Gathering & Order Night 2026',
                'venue_name' => 'Ballroom Grand Hotel • Meja Undangan',
                'registration_schedule' => 'Meja Registrasi Buka: 08:30 – 13:00 WIB',
                'registration_help_note' => 'PIC Meja Bantuan siap melayani pencarian manual ID di Area Foyer Ballroom.',
                'session_start_at' => now(),
                'session_end_at' => now()->addHours(2),
                'is_ordering_active' => true,
                'is_leaderboard_visible' => true,
                'doorprize_announcement' => 'DOORPRIZE EXCLUSIVE: Grand Prize Emas Logam Mulia & Paket Liburan',
                'running_ticker_text' => '🔥 Flash Deal Sesi PO Gathering SMS Sedang Berlangsung! Kumpulkan Order Sebanyak-banyaknya untuk Memenangkan Grand Prize Most Spender Malam Ini!',
            ]
        );
    }
}
