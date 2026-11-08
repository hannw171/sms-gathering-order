<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\EventSetting;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Services\GatheringService;
use App\Services\RewardCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        protected GatheringService $gatheringService,
        protected RewardCalculationService $rewardService
    ) {}

    public function cart(Request $request): Response|RedirectResponse
    {
        $pharmacyId = $request->session()->get('guest_pharmacy_id');
        if (! $pharmacyId) {
            return redirect()->route('guest.login');
        }

        $pharmacy = Pharmacy::with(['orders' => function ($q) {
            $q->where('status', '!=', 'cancelled')
                ->with(['items.product.manufacturer']);
        }])->find($pharmacyId);

        if (! $pharmacy) {
            return redirect()->route('guest.login');
        }

        $milestone = $this->gatheringService->getPharmacyMilestone($pharmacy);
        $currentRewards = $this->rewardService->calculateForPharmacy($pharmacy);
        $eventSetting = EventSetting::current();

        return Inertia::render('Guest/Cart', [
            'pharmacy' => $milestone['pharmacy'],
            'milestone' => $milestone,
            'currentRewards' => $currentRewards,
            'rewardOptions' => [
                'interbat' => RewardCalculationService::INTERBAT_OPTIONS,
                'graha_neo' => RewardCalculationService::GRAHA_NEO_OPTIONS,
                'exeltis_lanso' => RewardCalculationService::EXELTIS_LANSO_TIERS,
                'exeltis_sucra' => RewardCalculationService::EXELTIS_SUCRA_TIERS,
            ],
            'manufacturers' => Manufacturer::where('is_active', true)->get(),
            'event' => [
                'name' => $eventSetting->event_name,
                'session_start_at' => $eventSetting->session_start_at,
                'session_end_at' => $eventSetting->session_end_at,
                'is_ordering_active' => $eventSetting->is_ordering_active,
                'is_ordering_open' => $eventSetting->isOrderingOpen(),
                'ordering_status' => $eventSetting->getOrderingStatus(),
            ],
        ]);
    }

    public function checkout(Request $request)
    {
        $pharmacyId = $request->session()->get('guest_pharmacy_id');
        if (! $pharmacyId) {
            return redirect()->route('guest.login');
        }

        $pharmacy = Pharmacy::findOrFail($pharmacyId);

        $eventSetting = EventSetting::current();
        if ($eventSetting->session_start_at && now()->lt($eventSetting->session_start_at)) {
            $msg = 'Sesi pemesanan belum dimulai (akan dimulai pukul '.$eventSetting->session_start_at->format('H:i').' WIB).';

            return back()->withErrors(['error' => $msg]);
        }

        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
            'reward_selections' => ['nullable', 'array'],
        ]);

        // Validate manufacturer-specific session availability, min_order, and step
        $productIds = array_values(array_filter(array_map(fn ($item) => (int) ($item['product_id'] ?? 0), $request->items)));
        $productsById = Product::with('manufacturer')
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        foreach ($request->items as $item) {
            $product = $productsById->get((int) $item['product_id']) ?? Product::with('manufacturer')->findOrFail($item['product_id']);
            if ($product->manufacturer && ! $product->manufacturer->isOrderingOpen()) {
                $mStatus = $product->manufacturer->getOrderingStatus();
                $mName = $product->manufacturer->name;
                $msg = $mStatus === 'ended'
                    ? "Sesi pemesanan untuk pabrikan {$mName} telah berakhir."
                    : "Sesi pemesanan untuk pabrikan {$mName} sedang ditutup oleh panitia.";

                return back()->withErrors(['error' => $msg]);
            }

            $qty = (int) $item['quantity'];
            if ($product->min_order > 1 && $qty < $product->min_order) {
                return back()->withErrors(['error' => "Produk {$product->name} wajib dipesan minimal {$product->min_order} {$product->uom}."]);
            }

            if ($product->step > 1 && ($qty % $product->step !== 0)) {
                return back()->withErrors(['error' => "Pemesanan {$product->name} harus dalam kelipatan {$product->step} {$product->uom}."]);
            }
        }

        $order = DB::transaction(function () use ($request, $pharmacy, $productsById) {
            $totalAmount = 0;
            $totalItems = 0;
            $orderItemsData = [];

            foreach ($request->items as $item) {
                $product = $productsById->get((int) $item['product_id']) ?? Product::with('manufacturer')->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                $subtotal = (float) $product->price * $qty;

                $totalAmount += $subtotal;
                $totalItems += $qty;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'manufacturer_name' => $product->manufacturer->name,
                    'uom' => $product->uom,
                    'price' => $product->price,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            // Generate unique Order Number: PO-YYYYMMDD-XXXX
            $datePrefix = date('Ymd');
            $countToday = Order::whereDate('created_at', today())->count() + 1;
            $orderNumber = sprintf('PO-%s-%04d', $datePrefix, $countToday);

            $order = Order::create([
                'order_number' => $orderNumber,
                'pharmacy_id' => $pharmacy->id,
                'total_amount' => $totalAmount,
                'total_items' => $totalItems,
                'status' => 'confirmed',
                'notes' => $request->notes,
                'reward_selections' => $request->reward_selections,
            ]);

            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            return $order;
        });

        return redirect()->route('guest.order.success', ['order' => $order->id]);
    }

    public function success(Request $request, Order $order): Response|RedirectResponse
    {
        $pharmacyId = $request->session()->get('guest_pharmacy_id');
        if (! $pharmacyId || $order->pharmacy_id != $pharmacyId) {
            return redirect()->route('guest.catalog');
        }

        $order->load(['items', 'pharmacy', 'unlockedRewardTier']);
        $milestone = $this->gatheringService->getPharmacyMilestone($order->pharmacy);
        $cumulativeRewards = $this->rewardService->calculateForPharmacy($order->pharmacy);

        return Inertia::render('Guest/Success', [
            'order' => $order,
            'milestone' => $milestone,
            'rewards' => $cumulativeRewards,
            'pharmacy' => $milestone['pharmacy'],
        ]);
    }

    public function history(Request $request): Response|RedirectResponse
    {
        $pharmacyId = $request->session()->get('guest_pharmacy_id');
        if (! $pharmacyId) {
            return redirect()->route('guest.login');
        }

        $pharmacy = Pharmacy::with(['orders' => function ($q) {
            $q->where('status', '!=', 'cancelled')
                ->with(['items.product.manufacturer']);
        }])->find($pharmacyId);

        if (! $pharmacy) {
            return redirect()->route('guest.login');
        }

        $orders = $pharmacy->orders()
            ->with(['items.product'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Order $order): array {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => (float) $order->total_amount,
                    'total_items' => (int) $order->total_items,
                    'status' => $order->status,
                    'notes' => $order->notes,
                    'reward_selections' => $order->reward_selections ?? [],
                    'created_at_formatted' => $order->created_at?->format('H:i').' WIB',
                    'created_date_formatted' => $order->created_at?->translatedFormat('d M Y, H:i').' WIB',
                    'items' => $order->items->map(fn (OrderItem $item): array => [
                        'id' => $item->id,
                        'sku' => $item->product?->sku ?? '-',
                        'product_name' => $item->product_name,
                        'manufacturer_name' => $item->manufacturer_name,
                        'uom' => $item->uom,
                        'price' => (float) $item->price,
                        'quantity' => (int) $item->quantity,
                        'subtotal' => (float) $item->subtotal,
                    ])->values()->all(),
                ];
            });

        $milestone = $this->gatheringService->getPharmacyMilestone($pharmacy);
        $cumulativeRewards = $this->rewardService->calculateForPharmacy($pharmacy);

        return Inertia::render('Guest/Orders', [
            'pharmacy' => array_merge($milestone['pharmacy'], [
                'is_reward_handed_over' => ! is_null($pharmacy->reward_handed_over_at),
                'reward_handed_over_at' => $pharmacy->reward_handed_over_at?->format('H:i').' WIB',
                'reward_receiver_name' => $pharmacy->reward_receiver_name,
            ]),
            'milestone' => $milestone,
            'rewards' => $cumulativeRewards,
            'orders' => $orders,
        ]);
    }

    public function previewRewards(Request $request): JsonResponse
    {
        $pharmacyId = $request->session()->get('guest_pharmacy_id');
        $pharmacy = $request->attributes->get('guest_pharmacy') ?? ($pharmacyId ? Pharmacy::find($pharmacyId) : null);
        $items = $request->input('items', []);

        $result = $this->rewardService->evaluateCart($items, $pharmacy);

        return response()->json($result);
    }
}
