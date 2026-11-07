<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manufacturer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'sort_order',
        'is_active',
        'is_ordering_active',
        'session_end_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_ordering_active' => 'boolean',
        'session_end_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'is_ordering_open',
        'ordering_status',
    ];

    public function getIsOrderingOpenAttribute(): bool
    {
        return $this->isOrderingOpen();
    }

    public function getOrderingStatusAttribute(): string
    {
        return $this->getOrderingStatus();
    }

    public function isOrderingOpen(): bool
    {
        if (! $this->is_ordering_active) {
            return false;
        }

        if ($this->session_end_at && now()->gt($this->session_end_at)) {
            return false;
        }

        return true;
    }

    public function getOrderingStatus(): string
    {
        if (! $this->is_ordering_active) {
            return 'closed';
        }

        if ($this->session_end_at && now()->gt($this->session_end_at)) {
            return 'ended';
        }

        return 'open';
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
