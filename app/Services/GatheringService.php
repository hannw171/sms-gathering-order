<?php

namespace App\Services;

use App\Models\EventSetting;
use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\RewardTier;
use Illuminate\Support\Collection;

class GatheringService
{
    /**
     * Get the real-time leaderboard of pharmacies sorted by total spending.
     */
    public function getLeaderboard(): Collection
    {
        $pharmacies = Pharmacy::with(['orders' => function ($q) {
            $q->where('status', '!=', 'cancelled');
        }])->get();

        $ranked = $pharmacies->map(function ($pharmacy) {
            $totalSpending = (float) $pharmacy->orders->sum('total_amount');
            $ordersCount = $pharmacy->orders->count();
            $lastOrderAt = $pharmacy->orders->max('created_at');

            return [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
                'customer_code' => $pharmacy->customer_code,
                'table_number' => $pharmacy->table_number,
                'city' => $pharmacy->city,
                'total_spending' => $totalSpending,
                'orders_count' => $ordersCount,
                'last_order_at' => $lastOrderAt,
            ];
        })
        ->sortByDesc('total_spending')
        ->values();

        // Assign Rank (1, 2, 3, ...)
        return $ranked->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });
    }

    /**
     * Calculate reward milestones for a specific pharmacy.
     */
    public function getPharmacyMilestone(Pharmacy $pharmacy): array
    {
        $leaderboard = $this->getLeaderboard();
        $rankEntry = $leaderboard->firstWhere('id', $pharmacy->id);
        $currentRank = $rankEntry ? $rankEntry['rank'] : $leaderboard->count() + 1;
        $totalSpending = $rankEntry ? $rankEntry['total_spending'] : 0.0;

        $tiers = RewardTier::orderBy('min_spending', 'asc')->get();

        $unlockedTier = null;
        $nextTier = null;

        foreach ($tiers as $tier) {
            if ($totalSpending >= (float) $tier->min_spending) {
                $unlockedTier = $tier;
            } elseif (!$nextTier) {
                $nextTier = $tier;
            }
        }

        $progressPercentage = 100;
        $amountToNextTier = 0.0;

        if ($nextTier) {
            $baseSpending = $unlockedTier ? (float) $unlockedTier->min_spending : 0.0;
            $targetSpending = (float) $nextTier->min_spending;
            $range = max(1, $targetSpending - $baseSpending);
            $currentInRange = max(0, $totalSpending - $baseSpending);
            $progressPercentage = min(100, round(($currentInRange / $range) * 100, 1));
            $amountToNextTier = max(0, $targetSpending - $totalSpending);
        }

        return [
            'pharmacy' => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
                'customer_code' => $pharmacy->customer_code,
                'table_number' => $pharmacy->table_number,
                'city' => $pharmacy->city,
            ],
            'current_rank' => $currentRank,
            'total_spending' => $totalSpending,
            'unlocked_tier' => $unlockedTier,
            'next_tier' => $nextTier,
            'progress_percentage' => $progressPercentage,
            'amount_to_next_tier' => $amountToNextTier,
            'all_tiers' => $tiers,
        ];
    }
}
