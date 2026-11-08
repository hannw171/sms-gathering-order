<?php

namespace App\Services;

use App\Models\Manufacturer;
use App\Models\Pharmacy;
use App\Models\RewardTier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GatheringService
{
    public const GRAHA_GRAND_PRIZES = [
        1 => '1 Unit Smart TV Polytron 32 Inch',
        2 => '1 Unit Dispenser Cosmos Galon Bawah',
        3 => '1 Unit Kipas Angin Cosmos',
    ];

    protected const CACHE_KEY_ALL_LEADERBOARDS = 'gathering_all_leaderboards_v2';

    protected const CACHE_TTL_SECONDS = 60;

    /**
     * Get the real-time leaderboard of pharmacies sorted by total spending.
     * If $manufacturerId is provided, spending is filtered to products from that manufacturer.
     */
    public function getLeaderboard(?int $manufacturerId = null): Collection
    {
        $all = $this->getAllLeaderboards();

        if ($manufacturerId === null) {
            return $all['overall'];
        }

        return $all['by_manufacturer'][$manufacturerId] ?? collect();
    }

    /**
     * Get leaderboards for all active manufacturers plus overall.
     *
     * @return array{
     *     overall: Collection,
     *     by_manufacturer: array<int, Collection>,
     *     stats_by_manufacturer: array<int, array{total_gmv: float}>,
     *     manufacturers: Collection,
     *     total_gmv: float,
     *     total_orders: int
     * }
     */
    public function getAllLeaderboards(): array
    {
        $manufacturers = Manufacturer::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $cachedData = Cache::remember(self::CACHE_KEY_ALL_LEADERBOARDS, self::CACHE_TTL_SECONDS, function () use ($manufacturers): array {
            $pharmacies = Pharmacy::with(['orders' => function ($q) {
                $q->where('status', '!=', 'cancelled')
                    ->with(['items' => function ($iq) {
                        $iq->with('product');
                    }]);
            }])->get();

            // Calculate Overall
            $allRanked = $pharmacies->map(function ($pharmacy) {
                $totalSpending = (float) $pharmacy->orders->sum('total_amount');
                $firstOrder = $pharmacy->orders->min('created_at');
                $lastOrder = $pharmacy->orders->max('created_at');

                return [
                    'id' => (int) $pharmacy->id,
                    'name' => (string) $pharmacy->name,
                    'customer_code' => (string) $pharmacy->customer_code,
                    'table_number' => $pharmacy->table_number ? (string) $pharmacy->table_number : null,
                    'city' => (string) $pharmacy->city,
                    'total_spending' => (float) $totalSpending,
                    'orders_count' => (int) $pharmacy->orders->count(),
                    'first_order_at' => $firstOrder ? $firstOrder->format('Y-m-d H:i:s') : null,
                    'last_order_at' => $lastOrder ? $lastOrder->format('Y-m-d H:i:s') : null,
                    'is_checked_in' => (bool) $pharmacy->is_checked_in,
                    'checked_in_at' => $pharmacy->checked_in_at ? $pharmacy->checked_in_at->format('Y-m-d H:i:s') : null,
                ];
            })
                ->sort(function ($a, $b) {
                    if ($b['total_spending'] !== $a['total_spending']) {
                        return $b['total_spending'] <=> $a['total_spending'];
                    }

                    return ($a['first_order_at'] ?? '9999-99-99') <=> ($b['first_order_at'] ?? '9999-99-99');
                })
                ->values()
                ->map(function ($item, $index) {
                    $item['rank'] = $index + 1;

                    return $item;
                })
                ->all();

            // Calculate for each Manufacturer in memory
            $byManufacturer = [];
            $statsByManufacturer = [];

            foreach ($manufacturers as $manufacturer) {
                $mId = (int) $manufacturer->id;
                $isGraha = $manufacturer->slug === 'graha-farma';

                $mRanked = $pharmacies->map(function ($pharmacy) use ($mId, $isGraha) {
                    $matchingItems = $pharmacy->orders->flatMap->items
                        ->filter(function ($item) use ($mId) {
                            return $item->product && (int) $item->product->manufacturer_id === $mId;
                        });

                    $totalSpending = (float) $matchingItems->sum('subtotal');

                    $regulerBox = 0;
                    $paketCount = 0;
                    if ($isGraha) {
                        $regulerBox = (int) $matchingItems->filter(fn ($item) => ($item->product?->sku ?? '') !== 'GRH-019')->sum('quantity');
                        $paketCount = (int) floor($regulerBox / 99);
                    }

                    $firstOrder = $pharmacy->orders->min('created_at');
                    $lastOrder = $pharmacy->orders->max('created_at');

                    return [
                        'id' => (int) $pharmacy->id,
                        'name' => (string) $pharmacy->name,
                        'customer_code' => (string) $pharmacy->customer_code,
                        'table_number' => $pharmacy->table_number ? (string) $pharmacy->table_number : null,
                        'city' => (string) $pharmacy->city,
                        'total_spending' => (float) $totalSpending,
                        'paket_count' => (int) $paketCount,
                        'reguler_box' => (int) $regulerBox,
                        'orders_count' => (int) $pharmacy->orders->count(),
                        'first_order_at' => $firstOrder ? $firstOrder->format('Y-m-d H:i:s') : null,
                        'last_order_at' => $lastOrder ? $lastOrder->format('Y-m-d H:i:s') : null,
                        'is_checked_in' => (bool) $pharmacy->is_checked_in,
                        'checked_in_at' => $pharmacy->checked_in_at ? $pharmacy->checked_in_at->format('Y-m-d H:i:s') : null,
                    ];
                })
                    ->sort(function ($a, $b) use ($isGraha) {
                        if ($isGraha && $b['paket_count'] !== $a['paket_count']) {
                            return $b['paket_count'] <=> $a['paket_count'];
                        }

                        if ($b['total_spending'] !== $a['total_spending']) {
                            return $b['total_spending'] <=> $a['total_spending'];
                        }

                        return ($a['first_order_at'] ?? '9999-99-99') <=> ($b['first_order_at'] ?? '9999-99-99');
                    })
                    ->values()
                    ->map(function ($item, $index) use ($isGraha) {
                        $rank = $index + 1;
                        $item['rank'] = $rank;

                        if ($isGraha && $rank <= 3 && $item['paket_count'] >= 1) {
                            $item['grand_prize'] = self::GRAHA_GRAND_PRIZES[$rank] ?? null;
                            $item['grand_prize_title'] = "Juara {$rank}";
                        }

                        return $item;
                    })
                    ->all();

                $byManufacturer[$mId] = $mRanked;
                $statsByManufacturer[$mId] = [
                    'total_gmv' => (float) array_sum(array_column($mRanked, 'total_spending')),
                ];
            }

            $totalGmv = (float) array_sum(array_column($allRanked, 'total_spending'));
            $totalOrders = (int) array_sum(array_column($allRanked, 'orders_count'));

            return [
                'overall' => $allRanked,
                'by_manufacturer' => $byManufacturer,
                'stats_by_manufacturer' => $statsByManufacturer,
                'total_gmv' => $totalGmv,
                'total_orders' => $totalOrders,
            ];
        });

        // Convert the primitive arrays back to Collections on the fly
        $overallCollection = collect($cachedData['overall'] ?? []);
        $byManufacturerCollections = [];
        foreach (($cachedData['by_manufacturer'] ?? []) as $mId => $list) {
            $byManufacturerCollections[$mId] = collect($list);
        }

        return [
            'overall' => $overallCollection,
            'by_manufacturer' => $byManufacturerCollections,
            'stats_by_manufacturer' => $cachedData['stats_by_manufacturer'] ?? [],
            'manufacturers' => $manufacturers,
            'total_gmv' => (float) ($cachedData['total_gmv'] ?? 0.0),
            'total_orders' => (int) ($cachedData['total_orders'] ?? 0),
        ];
    }

    /**
     * Flush all cached leaderboard results immediately (invoked when orders change).
     */
    public function flushLeaderboardCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALL_LEADERBOARDS);
        RewardCalculationService::clearGrandPrizeCache();
    }

    /**
     * Calculate reward milestones for a specific pharmacy.
     */
    public function getPharmacyMilestone(Pharmacy $pharmacy): array
    {
        $all = $this->getAllLeaderboards();
        $leaderboard = $all['overall'];
        $rankEntry = $leaderboard->firstWhere('id', $pharmacy->id);
        $currentRank = $rankEntry ? $rankEntry['rank'] : $leaderboard->count() + 1;
        $totalSpending = $rankEntry ? $rankEntry['total_spending'] : 0.0;

        $tiers = RewardTier::orderBy('min_spending', 'asc')->get();

        $unlockedTier = null;
        $nextTier = null;

        foreach ($tiers as $tier) {
            if ($totalSpending >= (float) $tier->min_spending) {
                $unlockedTier = $tier;
            } elseif (! $nextTier) {
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
