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

    protected const CACHE_KEY_ALL_LEADERBOARDS = 'gathering_all_leaderboards_v1';

    protected const CACHE_KEY_OVERALL_LEADERBOARD = 'gathering_overall_leaderboard_v1';

    protected const CACHE_KEY_REWARD_TIERS = 'gathering_reward_tiers_v1';

    protected const CACHE_TTL_SECONDS = 5;

    /**
     * Get the real-time leaderboard of pharmacies sorted by total spending.
     * If $manufacturerId is provided, spending is filtered to products from that manufacturer.
     */
    public function getLeaderboard(?int $manufacturerId = null): Collection
    {
        if ($manufacturerId === null) {
            return Cache::remember(self::CACHE_KEY_OVERALL_LEADERBOARD, self::CACHE_TTL_SECONDS, function (): Collection {
                return $this->computeLeaderboard(null);
            });
        }

        $all = $this->getAllLeaderboards();
        if (isset($all['by_manufacturer'][$manufacturerId])) {
            return $all['by_manufacturer'][$manufacturerId];
        }

        return $this->computeLeaderboard($manufacturerId);
    }

    /**
     * Compute leaderboard from database for overall or a specific manufacturer.
     */
    protected function computeLeaderboard(?int $manufacturerId = null): Collection
    {
        $pharmacies = Pharmacy::with(['orders' => function ($q) {
            $q->where('status', '!=', 'cancelled')
                ->with(['items' => function ($iq) {
                    $iq->with('product');
                }]);
        }])->get();

        $manufacturer = $manufacturerId ? Manufacturer::find($manufacturerId) : null;
        $isGraha = $manufacturer && $manufacturer->slug === 'graha-farma';

        $ranked = $pharmacies->map(function ($pharmacy) use ($manufacturerId, $isGraha) {
            $matchingItems = $pharmacy->orders->flatMap->items
                ->filter(function ($item) use ($manufacturerId) {
                    return ! $manufacturerId || ($item->product && (int) $item->product->manufacturer_id === (int) $manufacturerId);
                });

            if ($manufacturerId) {
                $totalSpending = (float) $matchingItems->sum('subtotal');
            } else {
                $totalSpending = (float) $pharmacy->orders->sum('total_amount');
            }

            $regulerBox = 0;
            $paketCount = 0;
            if ($isGraha) {
                $regulerBox = (int) $matchingItems->filter(fn ($item) => ($item->product?->sku ?? '') !== 'GRH-019')->sum('quantity');
                $paketCount = (int) floor($regulerBox / 99);
            }

            $ordersCount = $pharmacy->orders->count();
            $firstOrderAt = $pharmacy->orders->min('created_at');
            $lastOrderAt = $pharmacy->orders->max('created_at');

            return [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
                'customer_code' => $pharmacy->customer_code,
                'city' => $pharmacy->city,
                'total_spending' => $totalSpending,
                'paket_count' => $paketCount,
                'reguler_box' => $regulerBox,
                'orders_count' => $ordersCount,
                'first_order_at' => $firstOrderAt,
                'last_order_at' => $lastOrderAt,
                'is_checked_in' => (bool) $pharmacy->is_checked_in,
                'checked_in_at' => $pharmacy->checked_in_at,
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
            ->values();

        // Assign Rank (1, 2, 3, ...) and Grand Prize if Graha
        return $ranked->map(function ($item, $index) use ($isGraha) {
            $rank = $index + 1;
            $item['rank'] = $rank;

            if ($isGraha && $rank <= 3 && $item['paket_count'] >= 1) {
                $item['grand_prize'] = self::GRAHA_GRAND_PRIZES[$rank] ?? null;
                $item['grand_prize_title'] = "Juara {$rank}";
            }

            return $item;
        });
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

        $cachedRankings = Cache::remember(self::CACHE_KEY_ALL_LEADERBOARDS, self::CACHE_TTL_SECONDS, function () use ($manufacturers): array {
            $pharmacies = Pharmacy::with(['orders' => function ($q) {
                $q->where('status', '!=', 'cancelled')
                    ->with(['items' => function ($iq) {
                        $iq->with('product');
                    }]);
            }])->get();

            // Calculate Overall
            $allRanked = $pharmacies->map(function ($pharmacy) {
                $totalSpending = (float) $pharmacy->orders->sum('total_amount');

                return [
                    'id' => $pharmacy->id,
                    'name' => $pharmacy->name,
                    'customer_code' => $pharmacy->customer_code,
                    'table_number' => $pharmacy->table_number,
                    'city' => $pharmacy->city,
                    'total_spending' => $totalSpending,
                    'orders_count' => $pharmacy->orders->count(),
                    'first_order_at' => $pharmacy->orders->min('created_at'),
                    'last_order_at' => $pharmacy->orders->max('created_at'),
                    'is_checked_in' => (bool) $pharmacy->is_checked_in,
                    'checked_in_at' => $pharmacy->checked_in_at,
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
                });

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

                    return [
                        'id' => $pharmacy->id,
                        'name' => $pharmacy->name,
                        'customer_code' => $pharmacy->customer_code,
                        'table_number' => $pharmacy->table_number,
                        'city' => $pharmacy->city,
                        'total_spending' => $totalSpending,
                        'paket_count' => $paketCount,
                        'reguler_box' => $regulerBox,
                        'orders_count' => $pharmacy->orders->count(),
                        'first_order_at' => $pharmacy->orders->min('created_at'),
                        'last_order_at' => $pharmacy->orders->max('created_at'),
                        'is_checked_in' => (bool) $pharmacy->is_checked_in,
                        'checked_in_at' => $pharmacy->checked_in_at,
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
                    });

                $byManufacturer[$mId] = $mRanked;
                $statsByManufacturer[$mId] = [
                    'total_gmv' => (float) $mRanked->sum('total_spending'),
                ];
            }

            $totalGmv = (float) $allRanked->sum('total_spending');
            $totalOrders = (int) $allRanked->sum('orders_count');

            return [
                'overall' => $allRanked,
                'by_manufacturer' => $byManufacturer,
                'stats_by_manufacturer' => $statsByManufacturer,
                'total_gmv' => $totalGmv,
                'total_orders' => $totalOrders,
            ];
        });

        return [
            'overall' => $cachedRankings['overall'],
            'by_manufacturer' => $cachedRankings['by_manufacturer'],
            'stats_by_manufacturer' => $cachedRankings['stats_by_manufacturer'],
            'manufacturers' => $manufacturers,
            'total_gmv' => $cachedRankings['total_gmv'],
            'total_orders' => $cachedRankings['total_orders'],
        ];
    }

    /**
     * Flush all cached leaderboard results immediately (invoked when orders change).
     */
    public function flushLeaderboardCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALL_LEADERBOARDS);
        Cache::forget(self::CACHE_KEY_OVERALL_LEADERBOARD);
        Cache::forget(self::CACHE_KEY_REWARD_TIERS);
        RewardCalculationService::clearGrandPrizeCache();
    }

    /**
     * Calculate reward milestones for a specific pharmacy.
     */
    public function getPharmacyMilestone(Pharmacy $pharmacy): array
    {
        $allLeaderboards = Cache::get(self::CACHE_KEY_ALL_LEADERBOARDS);
        $leaderboard = $allLeaderboards ? $allLeaderboards['overall'] : $this->getLeaderboard();
        $rankEntry = $leaderboard->firstWhere('id', $pharmacy->id);
        $currentRank = $rankEntry ? $rankEntry['rank'] : $leaderboard->count() + 1;
        $totalSpending = $rankEntry ? $rankEntry['total_spending'] : 0.0;

        $tiers = Cache::remember(self::CACHE_KEY_REWARD_TIERS, 60, fn () => RewardTier::orderBy('min_spending', 'asc')->get());

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
