<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RewardCalculationService
{
    /**
     * Options for Interbat Choice Rewards
     */
    public const INTERBAT_OPTIONS = [
        'Elektrik Kettle',
        'Panci Kuah',
        'Panci Mini Listrik',
    ];

    /**
     * Options for Graha Farma Neo Protifed Choice Rewards
     */
    public const GRAHA_NEO_OPTIONS = [
        'Magic Com Cosmos',
        'Mixer Miyako',
    ];

    /**
     * Tiers for Exeltis Lansoprazole (EXE-001)
     */
    public const EXELTIS_LANSO_TIERS = [
        ['min_qty' => 5, 'reward' => 'Handuk 30 x 70cm'],
        ['min_qty' => 10, 'reward' => 'Payung Lipat'],
        ['min_qty' => 15, 'reward' => 'Tumbler'],
        ['min_qty' => 20, 'reward' => 'Voucher Rp 50.000,-'],
        ['min_qty' => 25, 'reward' => 'Wajan Hitam 24cm'],
        ['min_qty' => 30, 'reward' => 'Panci 18cm'],
        ['min_qty' => 40, 'reward' => 'Cooper Blender 2L'],
        ['min_qty' => 50, 'reward' => 'Speaker Aktif'],
        ['min_qty' => 100, 'reward' => 'Setrikaan'],
        ['min_qty' => 200, 'reward' => 'Panci Set 3in 1'],
    ];

    /**
     * Tiers for Exeltis Sucralfate (EXE-002, EXE-003)
     */
    public const EXELTIS_SUCRA_TIERS = [
        ['min_qty' => 5, 'reward' => 'Tas Lipat'],
        ['min_qty' => 10, 'reward' => 'Tumbler Gelas'],
        ['min_qty' => 15, 'reward' => 'Payung Panjang'],
        ['min_qty' => 20, 'reward' => 'Voucher Rp 50.000,-'],
        ['min_qty' => 25, 'reward' => 'TWS'],
        ['min_qty' => 30, 'reward' => 'Power Bank'],
        ['min_qty' => 40, 'reward' => 'Speaker Aktif'],
        ['min_qty' => 100, 'reward' => 'Tekko'],
        ['min_qty' => 200, 'reward' => 'Panci Set'],
    ];

    /**
     * Get eligible reward options for Exeltis Lansoprazole based on ordered quantity.
     * Highest unlocked tier is placed first.
     *
     * @return array<int, string>
     */
    public static function getExeltisLansoOptions(int $qty): array
    {
        $options = [];
        foreach (self::EXELTIS_LANSO_TIERS as $tier) {
            if ($qty >= $tier['min_qty']) {
                $options[] = "{$tier['reward']} (Tier {$tier['min_qty']} Box)";
            }
        }

        return array_reverse($options);
    }

    /**
     * Get highest unlocked reward for Exeltis Lansoprazole.
     */
    public static function getHighestExeltisLansoReward(int $qty): ?string
    {
        $options = self::getExeltisLansoOptions($qty);

        return $options[0] ?? null;
    }

    /**
     * Get eligible reward options for Exeltis Sucralfate based on ordered quantity.
     * Highest unlocked tier is placed first.
     *
     * @return array<int, string>
     */
    public static function getExeltisSucraOptions(int $qty): array
    {
        $options = [];
        foreach (self::EXELTIS_SUCRA_TIERS as $tier) {
            if ($qty >= $tier['min_qty']) {
                $options[] = "{$tier['reward']} (Tier {$tier['min_qty']} Botol)";
            }
        }

        return array_reverse($options);
    }

    /**
     * Get highest unlocked reward for Exeltis Sucralfate.
     */
    public static function getHighestExeltisSucraReward(int $qty): ?string
    {
        $options = self::getExeltisSucraOptions($qty);

        return $options[0] ?? null;
    }

    /**
     * Cache for Graha Farma Grand Prize winners: [pharmacy_id => prize_info]
     *
     * @var array<int, array{rank: int, prize: string, paket_count: int, reguler_box: int}>|null
     */
    protected static ?array $grahaGrandPrizeWinnersCache = null;

    public static function clearGrandPrizeCache(): void
    {
        self::$grahaGrandPrizeWinnersCache = null;
    }

    /**
     * Determine Top 3 Grand Prize winners for PT. GRAHA FARMA based on regular package count.
     *
     * @return array<int, array{rank: int, prize: string, paket_count: int, reguler_box: int}>
     */
    public function getGrahaGrandPrizeWinners(): array
    {
        if (self::$grahaGrandPrizeWinnersCache !== null) {
            return self::$grahaGrandPrizeWinnersCache;
        }

        $pharmacies = Pharmacy::whereHas('orders', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->with(['orders' => function ($q) {
                $q->where('status', '!=', 'cancelled')
                    ->with(['items.product']);
            }])
            ->get();

        $ranked = $pharmacies->map(function ($pharmacy) {
            $grahaRegulerBox = 0;
            foreach ($pharmacy->orders as $order) {
                foreach ($order->items as $item) {
                    $sku = $item->product?->sku ?? '';
                    if (str_starts_with($sku, 'GRH-') && $sku !== 'GRH-019') {
                        $grahaRegulerBox += (int) $item->quantity;
                    }
                }
            }

            $paketCount = (int) floor($grahaRegulerBox / 99);
            $firstOrderAt = $pharmacy->orders->min('created_at');

            return [
                'pharmacy_id' => $pharmacy->id,
                'paket_count' => $paketCount,
                'reguler_box' => $grahaRegulerBox,
                'first_order_at' => $firstOrderAt,
            ];
        })
            ->filter(fn ($item) => $item['paket_count'] >= 1)
            ->sort(function ($a, $b) {
                if ($b['paket_count'] !== $a['paket_count']) {
                    return $b['paket_count'] <=> $a['paket_count'];
                }
                if ($b['reguler_box'] !== $a['reguler_box']) {
                    return $b['reguler_box'] <=> $a['reguler_box'];
                }

                return ($a['first_order_at'] ?? '9999-99-99') <=> ($b['first_order_at'] ?? '9999-99-99');
            })
            ->values()
            ->take(3);

        $prizes = [
            1 => '1 Unit Smart TV Polytron 32 Inch (Grand Prize Graha Farma #1)',
            2 => '1 Unit Dispenser Cosmos Galon Bawah (Grand Prize Graha Farma #2)',
            3 => '1 Unit Kipas Angin Cosmos (Grand Prize Graha Farma #3)',
        ];

        $winners = [];
        foreach ($ranked as $idx => $entry) {
            $rank = $idx + 1;
            $winners[$entry['pharmacy_id']] = [
                'rank' => $rank,
                'prize' => $prizes[$rank],
                'paket_count' => $entry['paket_count'],
                'reguler_box' => $entry['reguler_box'],
            ];
        }

        self::$grahaGrandPrizeWinnersCache = $winners;

        return self::$grahaGrandPrizeWinnersCache;
    }

    /**
     * Calculate cumulative rewards for a pharmacy across all active orders.
     */
    public function calculateForPharmacy(Pharmacy $pharmacy): array
    {
        if ($pharmacy->relationLoaded('orders')) {
            $orders = $pharmacy->orders->where('status', '!=', 'cancelled');
        } else {
            $orders = $pharmacy->orders()
                ->where('status', '!=', 'cancelled')
                ->with(['items.product.manufacturer'])
                ->get();
        }

        $results = $this->evaluateOrders($orders);

        // Inject Grand Prize for Graha Farma if this pharmacy is in Top 3
        $grandPrizeWinners = $this->getGrahaGrandPrizeWinners();
        if (isset($grandPrizeWinners[$pharmacy->id])) {
            $winner = $grandPrizeWinners[$pharmacy->id];
            $results['physical_rewards'][$winner['prize']] = 1;
        }

        return $results;
    }

    /**
     * Check if a pharmacy has earned any reward or voucher.
     */
    public function hasReward(Pharmacy $pharmacy): bool
    {
        $rewards = $this->calculateForPharmacy($pharmacy);

        return
            ($rewards['total_vouchers'] ?? 0) > 0 ||
            ! empty($rewards['physical_rewards']) ||
            ($rewards['choice_quotas']['interbat'] ?? 0) > 0 ||
            ($rewards['choice_quotas']['graha_neo'] ?? 0) > 0 ||
            ! empty($rewards['choice_selections']);
    }

    /**
     * Get reward summaries for all pharmacies.
     *
     * @return Collection<int, array{pharmacy: Pharmacy, total_spending: float, order_count: int, rewards: array, has_reward: bool, is_handed_over: bool, handed_over_at: ?Carbon, receiver_name: ?string, handover_notes: ?string}>
     */
    public function getAllPharmaciesRewardsSummary(): Collection
    {
        $grandPrizeWinners = $this->getGrahaGrandPrizeWinners();

        $pharmacies = Pharmacy::with([
            'orders' => function ($q) {
                $q->where('status', '!=', 'cancelled')
                    ->with(['items.product.manufacturer']);
            },
        ])->orderBy('customer_code')->get();

        return $pharmacies->map(function (Pharmacy $pharmacy) use ($grandPrizeWinners) {
            $orders = $pharmacy->orders;
            $rewards = $this->evaluateOrders($orders);

            if (isset($grandPrizeWinners[$pharmacy->id])) {
                $winner = $grandPrizeWinners[$pharmacy->id];
                $rewards['physical_rewards'][$winner['prize']] = 1;
            }

            $totalSpending = (float) $orders->sum('total_amount');
            $hasReward = (
                ($rewards['total_vouchers'] ?? 0) > 0 ||
                ! empty($rewards['physical_rewards']) ||
                ($rewards['choice_quotas']['interbat'] ?? 0) > 0 ||
                ($rewards['choice_quotas']['graha_neo'] ?? 0) > 0 ||
                ! empty($rewards['choice_selections'])
            );

            return [
                'pharmacy' => $pharmacy,
                'total_spending' => $totalSpending,
                'order_count' => $orders->count(),
                'rewards' => $rewards,
                'has_reward' => $hasReward,
                'is_handed_over' => ! is_null($pharmacy->reward_handed_over_at),
                'handed_over_at' => $pharmacy->reward_handed_over_at,
                'receiver_name' => $pharmacy->reward_receiver_name,
                'handover_notes' => $pharmacy->reward_handover_notes,
            ];
        });
    }

    /**
     * Calculate reward preview for a checkout cart combined with pharmacy historical orders.
     *
     * @param  array<int, array{product_id: int, quantity: int, price: float, name: string}>  $cartItems
     */
    public function evaluateCart(array $cartItems, ?Pharmacy $pharmacy = null): array
    {
        // 1. Calculate previous rewards
        $previousRewards = $pharmacy ? $this->calculateForPharmacy($pharmacy) : $this->getEmptyRewardSummary();

        // 2. Aggregate cart items by manufacturer and product
        $cartAggregates = $this->aggregateCartItems($cartItems);

        // 3. Combined aggregates
        $combinedSpending = $previousRewards['spending_by_manufacturer'];
        $combinedQty = $previousRewards['qty_by_sku'];

        foreach ($cartAggregates['spending_by_manufacturer'] as $mSlug => $amt) {
            $combinedSpending[$mSlug] = ($combinedSpending[$mSlug] ?? 0.0) + $amt;
        }

        foreach ($cartAggregates['qty_by_sku'] as $sku => $qty) {
            $combinedQty[$sku] = ($combinedQty[$sku] ?? 0) + $qty;
        }

        $projectedRewards = $this->evaluateFromAggregates($combinedSpending, $combinedQty, $cartAggregates['selected_choices']);

        // Detect new choice rewards unlocked in this specific cart
        $newInterbatChoices = max(0, $projectedRewards['choice_quotas']['interbat'] - $previousRewards['choice_quotas']['interbat']);
        $newGrahaNeoChoices = max(0, $projectedRewards['choice_quotas']['graha_neo'] - $previousRewards['choice_quotas']['graha_neo']);

        // Detect Exeltis choices unlocked in this specific cart
        $cartQLanso = $cartAggregates['qty_by_sku']['EXE-001'] ?? 0;
        $cartQSucra = ($cartAggregates['qty_by_sku']['EXE-002'] ?? 0) + ($cartAggregates['qty_by_sku']['EXE-003'] ?? 0);

        $exeltisLansoChoices = $cartQLanso >= 5 ? 1 : 0;
        $exeltisSucraChoices = $cartQSucra >= 5 ? 1 : 0;

        $exeltisLansoOptions = $cartQLanso >= 5 ? self::getExeltisLansoOptions($cartQLanso) : [];
        $exeltisSucraOptions = $cartQSucra >= 5 ? self::getExeltisSucraOptions($cartQSucra) : [];

        return [
            'previous' => $previousRewards,
            'projected' => $projectedRewards,
            'unlocked_now' => [
                'interbat_choice_count' => $newInterbatChoices,
                'graha_neo_choice_count' => $newGrahaNeoChoices,
                'exeltis_lanso_choice_count' => $exeltisLansoChoices,
                'exeltis_lanso_qty' => $cartQLanso,
                'exeltis_lanso_options' => $exeltisLansoOptions,
                'exeltis_sucra_choice_count' => $exeltisSucraChoices,
                'exeltis_sucra_qty' => $cartQSucra,
                'exeltis_sucra_options' => $exeltisSucraOptions,
                'interbat_options' => self::INTERBAT_OPTIONS,
                'graha_neo_options' => self::GRAHA_NEO_OPTIONS,
            ],
        ];
    }

    /**
     * Evaluate a collection of active orders.
     */
    protected function evaluateOrders(Collection $orders): array
    {
        $spendingByM = [];
        $qtyBySku = [];
        $selectedChoices = [];

        foreach ($orders as $order) {
            // Collect user choice selections from order
            if (! empty($order->reward_selections) && is_array($order->reward_selections)) {
                $selectedChoices[] = $order->reward_selections;
            }

            foreach ($order->items as $item) {
                $mSlug = $item->product?->manufacturer?->slug ?? 'lainnya';
                $sku = $item->product?->sku ?? $item->product_name;

                $spendingByM[$mSlug] = ($spendingByM[$mSlug] ?? 0.0) + (float) $item->subtotal;
                $qtyBySku[$sku] = ($qtyBySku[$sku] ?? 0) + (int) $item->quantity;
            }
        }

        return $this->evaluateFromAggregates($spendingByM, $qtyBySku, $selectedChoices);
    }

    /**
     * Core evaluation logic from spending and SKU quantity maps.
     */
    public function evaluateFromAggregates(array $spendingByM, array $qtyBySku, array $selectedChoices = []): array
    {
        $vouchersByM = [];
        $physicalRewards = []; // [item_name => qty]
        $choiceQuotas = ['interbat' => 0, 'graha_neo' => 0];
        $details = [];

        // -------------------------------------------------------------
        // 1. PT. ZENITH PHARMA & BUFA (zenith-bufa)
        // -------------------------------------------------------------
        $zenithSpend = $spendingByM['zenith-bufa'] ?? 0.0;
        $zenithPaket = (int) floor($zenithSpend / 1500000);
        $zenithGifts = [];

        if ($zenithPaket === 1) {
            $zenithGifts[] = '1 Tumbler';
            $physicalRewards['Tumbler (Zenith Paket)'] = ($physicalRewards['Tumbler (Zenith Paket)'] ?? 0) + 1;
        } elseif ($zenithPaket >= 2) {
            $zenithGifts[] = "{$zenithPaket} Paket (Tumbler & Sarung)";
            $physicalRewards['Tumbler (Zenith Paket)'] = ($physicalRewards['Tumbler (Zenith Paket)'] ?? 0) + $zenithPaket;
            $physicalRewards['Sarung (Zenith Paket)'] = ($physicalRewards['Sarung (Zenith Paket)'] ?? 0) + $zenithPaket;
        }

        // Product specific: Flumin (ZNT-021), Zenpepsa (ZNT-045), Flamifa (ZNT-018)
        $fluminQty = $qtyBySku['ZNT-021'] ?? 0;
        $fluminBonus = (int) floor($fluminQty / 50);
        if ($fluminBonus > 0) {
            $zenithGifts[] = "{$fluminBonus} Chopper (Bonus Flumin Plus {$fluminQty} Box)";
            $physicalRewards['Chopper (Flumin Plus)'] = ($physicalRewards['Chopper (Flumin Plus)'] ?? 0) + $fluminBonus;
        }

        $zenpepsaQty = $qtyBySku['ZNT-045'] ?? 0;
        $zenpepsaBonus = (int) floor($zenpepsaQty / 180);
        if ($zenpepsaBonus > 0) {
            $zenithGifts[] = "{$zenpepsaBonus} Wajan Serbaguna (Bonus Zenpepsa {$zenpepsaQty} Fls)";
            $physicalRewards['Wajan Serbaguna (Zenpepsa)'] = ($physicalRewards['Wajan Serbaguna (Zenpepsa)'] ?? 0) + $zenpepsaBonus;
        }

        $flamifaQty = $qtyBySku['ZNT-018'] ?? 0;
        $flamifaBonus = (int) floor($flamifaQty / 75);
        if ($flamifaBonus > 0) {
            $zenithGifts[] = "{$flamifaBonus} Electric Cooker (Bonus Flamifa {$flamifaQty} Box)";
            $physicalRewards['Electric Cooker (Flamifa)'] = ($physicalRewards['Electric Cooker (Flamifa)'] ?? 0) + $flamifaBonus;
        }

        $details['zenith-bufa'] = [
            'name' => 'PT. ZENITH PHARMA & BUFA',
            'spending' => $zenithSpend,
            'paket_count' => $zenithPaket,
            'vouchers' => 0,
            'gifts' => $zenithGifts,
        ];

        // -------------------------------------------------------------
        // 2. PT. GRAHA FARMA (graha-farma)
        // -------------------------------------------------------------
        // Neo Protifed is GRH-019
        $neoProtifedQty = $qtyBySku['GRH-019'] ?? 0;
        $grahaRegulerBox = 0;
        foreach ($qtyBySku as $sku => $qty) {
            if (str_starts_with($sku, 'GRH-') && $sku !== 'GRH-019') {
                $grahaRegulerBox += $qty;
            }
        }

        $grahaPaket = (int) floor($grahaRegulerBox / 99);
        $grahaVoucher = 0;
        if ($grahaPaket >= 3) {
            $kelipatanTiga = (int) floor($grahaPaket / 3);
            $sisa = $grahaPaket % 3;
            $grahaVoucher = ($kelipatanTiga * 500000) + ($sisa * 100000);
        } elseif ($grahaPaket > 0) {
            $grahaVoucher = $grahaPaket * 100000;
        }

        $grahaGifts = [];
        if ($grahaPaket > 0) {
            $grahaGifts[] = 'Voucher Belanja Rp '.number_format($grahaVoucher, 0, ',', '.')." ({$grahaPaket} Paket @ 99 Box)";
        }

        // Neo Protifed promo: 25 box -> Voucher Rp 50.000 + Pilihan Magic Com / Mixer
        $neoPaket = (int) floor($neoProtifedQty / 25);
        $neoVoucher = $neoPaket * 50000;
        if ($neoPaket > 0) {
            $choiceQuotas['graha_neo'] += $neoPaket;
            $grahaGifts[] = "{$neoPaket}x Voucher Rp 50.000 & {$neoPaket}x Pilihan Magic Com Cosmos / Mixer Miyako (Neo Protifed {$neoProtifedQty} Box)";
        }

        $totalGrahaVoucher = $grahaVoucher + $neoVoucher;
        if ($totalGrahaVoucher > 0) {
            $vouchersByM['graha-farma'] = $totalGrahaVoucher;
        }

        $details['graha-farma'] = [
            'name' => 'PT. GRAHA FARMA',
            'spending' => $spendingByM['graha-farma'] ?? 0.0,
            'reguler_box' => $grahaRegulerBox,
            'paket_count' => $grahaPaket,
            'neo_protifed_qty' => $neoProtifedQty,
            'neo_paket' => $neoPaket,
            'vouchers' => $totalGrahaVoucher,
            'gifts' => $grahaGifts,
        ];

        // -------------------------------------------------------------
        // 3. PT. MEPRO (mepro)
        // -------------------------------------------------------------
        // MEP-001 (Cefixime 100), MEP-002 (Cefadroxil 500), MEP-003 (Mezac 50), MEP-004 (Intunal Forte)
        $qCefixime = $qtyBySku['MEP-001'] ?? 0;
        $qCefadroxil = $qtyBySku['MEP-002'] ?? 0;
        $qMezac = $qtyBySku['MEP-003'] ?? 0;
        $qIntunal = $qtyBySku['MEP-004'] ?? 0;

        $vCefixime = ((int) floor($qCefixime / 50)) * 100000;
        $vCefadroxil = ((int) floor($qCefadroxil / 50)) * 100000;
        $vMezac = ((int) floor($qMezac / 50)) * 50000;
        $vIntunal = ((int) floor($qIntunal / 50)) * 100000;

        $meproVoucher = $vCefixime + $vCefadroxil + $vMezac + $vIntunal;
        if ($meproVoucher > 0) {
            $vouchersByM['mepro'] = $meproVoucher;
        }

        $meproGifts = [];
        if ($meproVoucher > 0) {
            $meproGifts[] = 'Voucher Belanja Rp '.number_format($meproVoucher, 0, ',', '.');
        }

        // Bundle: PROGRAM DIAMBIL SEMUA (semua 4 produk @50 box) -> Magic Com
        $bundleMepro = min(
            (int) floor($qCefixime / 50),
            (int) floor($qCefadroxil / 50),
            (int) floor($qMezac / 50),
            (int) floor($qIntunal / 50)
        );

        if ($bundleMepro > 0) {
            $meproGifts[] = "{$bundleMepro} Buah Magic Com (Bundle 4 Produk Mepro @ 50 Box)";
            $physicalRewards['Magic Com (Mepro Bundle)'] = ($physicalRewards['Magic Com (Mepro Bundle)'] ?? 0) + $bundleMepro;
        }

        $details['mepro'] = [
            'name' => 'PT. MEPRO',
            'spending' => $spendingByM['mepro'] ?? 0.0,
            'vouchers' => $meproVoucher,
            'bundle_count' => $bundleMepro,
            'gifts' => $meproGifts,
        ];

        // -------------------------------------------------------------
        // 4. INTERBAT (interbat)
        // -------------------------------------------------------------
        $interbatSpend = $spendingByM['interbat'] ?? 0.0;
        $interbatPaket = (int) floor($interbatSpend / 2500000);
        $interbatVoucher = 0;

        if ($interbatPaket === 2) {
            $interbatVoucher = 50000;
        } elseif ($interbatPaket === 3) {
            $interbatVoucher = 125000;
        } elseif ($interbatPaket >= 4) {
            $interbatVoucher = 300000 * (int) floor($interbatPaket / 4);
        }

        if ($interbatVoucher > 0) {
            $vouchersByM['interbat'] = $interbatVoucher;
        }

        $interbatGifts = [];
        if ($interbatPaket > 0) {
            $choiceQuotas['interbat'] += $interbatPaket;
            $interbatGifts[] = "{$interbatPaket}x Bonus Pilihan (Kettle / Panci Kuah / Panci Mini)";
            if ($interbatVoucher > 0) {
                $interbatGifts[] = 'Tambahan Voucher Rp '.number_format($interbatVoucher, 0, ',', '.');
            }
        }

        $details['interbat'] = [
            'name' => 'INTERBAT',
            'spending' => $interbatSpend,
            'paket_count' => $interbatPaket,
            'vouchers' => $interbatVoucher,
            'gifts' => $interbatGifts,
        ];

        // -------------------------------------------------------------
        // 5. ERITA (erita) -> ETAPRIL (ERT-001)
        // -------------------------------------------------------------
        $eritaQty = $qtyBySku['ERT-001'] ?? 0;
        $eritaBonus = (int) floor($eritaQty / 100);
        $eritaGifts = [];
        if ($eritaBonus > 0) {
            $eritaGifts[] = "{$eritaBonus} Buah Rice Cooker ({$eritaQty} Box Etapril)";
            $physicalRewards['Rice Cooker (Erita)'] = ($physicalRewards['Rice Cooker (Erita)'] ?? 0) + $eritaBonus;
        }

        $details['erita'] = [
            'name' => 'ERITA',
            'spending' => $spendingByM['erita'] ?? 0.0,
            'qty' => $eritaQty,
            'gifts' => $eritaGifts,
            'vouchers' => 0,
        ];

        // -------------------------------------------------------------
        // 6. ARTO FARMA (arto-farma) -> ARFLAM 50MG (ART-001)
        // -------------------------------------------------------------
        $artoQty = $qtyBySku['ART-001'] ?? 0;
        $artoBonus = (int) floor($artoQty / 100);
        $artoGifts = [];
        if ($artoBonus > 0) {
            $artoGifts[] = "{$artoBonus} Kipas Angin Sogo ({$artoQty} Box Arflam)";
            $physicalRewards['Kipas Angin Sogo (Arto Farma)'] = ($physicalRewards['Kipas Angin Sogo (Arto Farma)'] ?? 0) + $artoBonus;
        }

        $details['arto-farma'] = [
            'name' => 'ARTO FARMA',
            'spending' => $spendingByM['arto-farma'] ?? 0.0,
            'qty' => $artoQty,
            'gifts' => $artoGifts,
            'vouchers' => 0,
        ];

        // -------------------------------------------------------------
        // 7. YARINDO (yarindo) -> FUROSEMIDE 40MG (YAR-001)
        // -------------------------------------------------------------
        $yarindoQty = $qtyBySku['YAR-001'] ?? 0;
        $yarindoBonus = (int) floor($yarindoQty / 78);
        $yarindoGifts = [];
        if ($yarindoBonus > 0) {
            $yarindoGifts[] = "{$yarindoBonus} Buah Kain Batik ({$yarindoQty} Box Furosemide)";
            $physicalRewards['Kain Batik (Yarindo)'] = ($physicalRewards['Kain Batik (Yarindo)'] ?? 0) + $yarindoBonus;
        }

        $details['yarindo'] = [
            'name' => 'YARINDO',
            'spending' => $spendingByM['yarindo'] ?? 0.0,
            'qty' => $yarindoQty,
            'gifts' => $yarindoGifts,
            'vouchers' => 0,
        ];

        // -------------------------------------------------------------
        // 8. DEXA (dexa)
        // DXA-001 (Dexamol 100 Box), DXA-002 (Verbetin 70 Fls), DXA-003 (Meticon 70 Fls)
        // -------------------------------------------------------------
        $qDexamol = $qtyBySku['DXA-001'] ?? 0;
        $qVerbetin = $qtyBySku['DXA-002'] ?? 0;
        $qMeticon = $qtyBySku['DXA-003'] ?? 0;

        $bDexamol = (int) floor($qDexamol / 100);
        $bVerbetin = (int) floor($qVerbetin / 70);
        $bMeticon = (int) floor($qMeticon / 70);

        $totalDexaBonus = $bDexamol + $bVerbetin + $bMeticon;
        $dexaVoucher = $totalDexaBonus * 50000;
        if ($dexaVoucher > 0) {
            $vouchersByM['dexa'] = $dexaVoucher;
        }

        $dexaGifts = [];
        if ($totalDexaBonus > 0) {
            $dexaGifts[] = "{$totalDexaBonus} Tumbler/Mug & Voucher Rp ".number_format($dexaVoucher, 0, ',', '.');
            $physicalRewards['Tumbler/Mug (Dexa)'] = ($physicalRewards['Tumbler/Mug (Dexa)'] ?? 0) + $totalDexaBonus;
        }

        $details['dexa'] = [
            'name' => 'DEXA',
            'spending' => $spendingByM['dexa'] ?? 0.0,
            'vouchers' => $dexaVoucher,
            'gifts' => $dexaGifts,
        ];

        // -------------------------------------------------------------
        // 9. EXELTIS (exeltis)
        // EXE-001 (Lansoprazole), EXE-002 (Sucralfate 60ml), EXE-003 (Sucralfate 100ml)
        // Per owner instruction: cukup perolehan Box & Fls saja, bebas pilih hadiah!
        // -------------------------------------------------------------
        $qLanso = $qtyBySku['EXE-001'] ?? 0;
        $qSucra = ($qtyBySku['EXE-002'] ?? 0) + ($qtyBySku['EXE-003'] ?? 0);

        $exeltisGifts = [];
        if ($qLanso > 0 || $qSucra > 0) {
            $exeltisGifts[] = "Total Order: {$qLanso} Box Lansoprazole & {$qSucra} Botol Sucralfate";
            $exeltisGifts[] = 'ANDA BEBAS MEMILIH HADIAH YANG ADA DI PROGRAM';
        }

        $details['exeltis'] = [
            'name' => 'EXELTIS',
            'spending' => $spendingByM['exeltis'] ?? 0.0,
            'lanso_box' => $qLanso,
            'sucra_fls' => $qSucra,
            'vouchers' => 0,
            'gifts' => $exeltisGifts,
            'program_note' => 'ANDA BEBAS MEMILIH HADIAH YANG ADA DI PROGRAM',
        ];

        // -------------------------------------------------------------
        // Resolve Selected Choices across orders
        // -------------------------------------------------------------
        $userChosenOptions = [
            'interbat' => [],
            'graha_neo' => [],
            'exeltis_lanso' => [],
            'exeltis_sucra' => [],
        ];

        foreach ($selectedChoices as $sel) {
            if (! is_array($sel)) {
                continue;
            }

            foreach ($sel as $k => $val) {
                $kLower = strtolower((string) $k);

                if (str_starts_with($kLower, 'interbat')) {
                    if (is_array($val)) {
                        foreach ($val as $item) {
                            if (! empty($item) && is_string($item)) {
                                $userChosenOptions['interbat'][] = $item;
                            }
                        }
                    } elseif (is_string($val) && ! empty($val)) {
                        $userChosenOptions['interbat'][] = $val;
                    }
                } elseif (str_starts_with($kLower, 'graha_neo') || str_starts_with($kLower, 'neo_protifed')) {
                    if (is_array($val)) {
                        foreach ($val as $item) {
                            if (! empty($item) && is_string($item)) {
                                $userChosenOptions['graha_neo'][] = $item;
                            }
                        }
                    } elseif (is_string($val) && ! empty($val)) {
                        $userChosenOptions['graha_neo'][] = $val;
                    }
                } elseif (str_contains($kLower, 'lanso')) {
                    $item = is_array($val) ? ($val[0] ?? null) : $val;
                    if (! empty($item) && is_string($item)) {
                        $userChosenOptions['exeltis_lanso'][] = $item;
                    }
                } elseif (str_contains($kLower, 'sucra')) {
                    $item = is_array($val) ? ($val[0] ?? null) : $val;
                    if (! empty($item) && is_string($item)) {
                        $userChosenOptions['exeltis_sucra'][] = $item;
                    }
                }
            }
        }

        // Auto-default to highest reached tier if tier reached but no specific choice recorded
        if ($qLanso >= 5 && empty($userChosenOptions['exeltis_lanso'])) {
            $highestLanso = self::getHighestExeltisLansoReward($qLanso);
            if ($highestLanso) {
                $userChosenOptions['exeltis_lanso'][] = $highestLanso;
            }
        }

        if ($qSucra >= 5 && empty($userChosenOptions['exeltis_sucra'])) {
            $highestSucra = self::getHighestExeltisSucraReward($qSucra);
            if ($highestSucra) {
                $userChosenOptions['exeltis_sucra'][] = $highestSucra;
            }
        }

        $choiceSelections = [];
        foreach ($userChosenOptions['interbat'] as $opt) {
            $choiceSelections[] = [
                'manufacturer_slug' => 'interbat',
                'selected_option' => $opt,
            ];
        }
        foreach ($userChosenOptions['graha_neo'] as $opt) {
            $choiceSelections[] = [
                'manufacturer_slug' => 'graha-farma',
                'selected_option' => $opt,
            ];
        }
        foreach ($userChosenOptions['exeltis_lanso'] as $opt) {
            $choiceSelections[] = [
                'manufacturer_slug' => 'exeltis',
                'selected_option' => $opt,
            ];
            if (str_contains($opt, 'Voucher Rp 50.000')) {
                $vouchersByM['exeltis'] = ($vouchersByM['exeltis'] ?? 0) + 50000;
            }
        }
        foreach ($userChosenOptions['exeltis_sucra'] as $opt) {
            $choiceSelections[] = [
                'manufacturer_slug' => 'exeltis',
                'selected_option' => $opt,
            ];
            if (str_contains($opt, 'Voucher Rp 50.000')) {
                $vouchersByM['exeltis'] = ($vouchersByM['exeltis'] ?? 0) + 50000;
            }
        }

        $totalVouchers = array_sum($vouchersByM);

        $choiceQuotas['exeltis_lanso'] = $qLanso >= 5 ? 1 : 0;
        $choiceQuotas['exeltis_sucra'] = $qSucra >= 5 ? 1 : 0;

        return [
            'total_vouchers' => $totalVouchers,
            'vouchers' => $totalVouchers,
            'vouchers_by_manufacturer' => $vouchersByM,
            'voucher_breakdown' => $vouchersByM,
            'physical_rewards' => $physicalRewards,
            'choice_quotas' => $choiceQuotas,
            'choice_rewards_earned' => $choiceQuotas,
            'user_chosen_options' => $userChosenOptions,
            'choice_selections' => $choiceSelections,
            'spending_by_manufacturer' => $spendingByM,
            'qty_by_sku' => $qtyBySku,
            'details' => $details,
        ];
    }

    /**
     * Helper to aggregate cart items into spending and SKU maps.
     */
    protected function aggregateCartItems(array $cartItems): array
    {
        $spendingByM = [];
        $qtyBySku = [];
        $selectedChoices = [];

        foreach ($cartItems as $item) {
            $product = Product::with('manufacturer')->find($item['product_id'] ?? 0);
            if (! $product) {
                continue;
            }

            $mSlug = $product->manufacturer?->slug ?? 'lainnya';
            $sku = $product->sku;
            $qty = (int) ($item['quantity'] ?? 0);
            $subtotal = $qty * (float) $product->price;

            $spendingByM[$mSlug] = ($spendingByM[$mSlug] ?? 0.0) + $subtotal;
            $qtyBySku[$sku] = ($qtyBySku[$sku] ?? 0) + $qty;
        }

        return [
            'spending_by_manufacturer' => $spendingByM,
            'qty_by_sku' => $qtyBySku,
            'selected_choices' => $selectedChoices,
        ];
    }

    /**
     * Empty summary fallback.
     */
    protected function getEmptyRewardSummary(): array
    {
        return [
            'total_vouchers' => 0,
            'vouchers' => 0,
            'vouchers_by_manufacturer' => [],
            'voucher_breakdown' => [],
            'physical_rewards' => [],
            'choice_quotas' => ['interbat' => 0, 'graha_neo' => 0, 'exeltis_lanso' => 0, 'exeltis_sucra' => 0],
            'choice_rewards_earned' => ['interbat' => 0, 'graha_neo' => 0, 'exeltis_lanso' => 0, 'exeltis_sucra' => 0],
            'user_chosen_options' => ['interbat' => [], 'graha_neo' => [], 'exeltis_lanso' => [], 'exeltis_sucra' => [],
            ],
            'choice_selections' => [],
            'spending_by_manufacturer' => [],
            'qty_by_sku' => [],
            'details' => [],
        ];
    }
}
