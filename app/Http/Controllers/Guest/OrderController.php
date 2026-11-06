<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\EventSetting;
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

        $pharmacy = Pharmacy::find($pharmacyId);
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
            'event' => [
                'name' => $eventSetting->event_name,
                'session_end_at' => $eventSetting->session_end_at,
                'is_ordering_active' => $eventSetting->is_ordering_active,
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
        if (! $eventSetting->is_ordering_active) {
            return back()->withErrors(['error' => 'Sesi pemesanan telah ditutup oleh panitia.']);
        }

        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
            'reward_selections' => ['nullable', 'array'],
        ]);

        $order = DB::transaction(function () use ($request, $pharmacy) {
            $totalAmount = 0;
            $totalItems = 0;
            $orderItemsData = [];

            foreach ($request->items as $item) {
                $product = Product::with('manufacturer')->findOrFail($item['product_id']);
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
                'ip_address' => $request->ip(),
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

    public function previewRewards(Request $request): JsonResponse
    {
        $pharmacyId = $request->session()->get('guest_pharmacy_id');
        $pharmacy = $pharmacyId ? Pharmacy::find($pharmacyId) : null;
        $items = $request->input('items', []);

        $result = $this->rewardService->evaluateCart($items, $pharmacy);

        return response()->json($result);
    }
}
