<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\EventSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Services\GatheringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        protected GatheringService $gatheringService
    ) {}

    public function cart(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $pharmacyId = $request->session()->get('guest_pharmacy_id');
        if (!$pharmacyId) {
            return redirect()->route('guest.login');
        }

        $pharmacy = Pharmacy::find($pharmacyId);
        if (!$pharmacy) {
            return redirect()->route('guest.login');
        }

        $milestone = $this->gatheringService->getPharmacyMilestone($pharmacy);
        $eventSetting = EventSetting::current();

        return Inertia::render('Guest/Cart', [
            'pharmacy' => $milestone['pharmacy'],
            'milestone' => $milestone,
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
        if (!$pharmacyId) {
            return redirect()->route('guest.login');
        }

        $pharmacy = Pharmacy::findOrFail($pharmacyId);

        $eventSetting = EventSetting::current();
        if (!$eventSetting->is_ordering_active) {
            return back()->withErrors(['error' => 'Sesi pemesanan telah ditutup oleh panitia.']);
        }

        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
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
                'ip_address' => $request->ip(),
            ]);

            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            // Update unlocked reward tier
            $milestone = $this->gatheringService->getPharmacyMilestone($pharmacy);
            if ($milestone['unlocked_tier']) {
                $order->update(['unlocked_reward_tier_id' => $milestone['unlocked_tier']->id]);
            }

            return $order;
        });

        return redirect()->route('guest.order.success', ['order' => $order->id]);
    }

    public function success(Request $request, Order $order): Response|\Illuminate\Http\RedirectResponse
    {
        $pharmacyId = $request->session()->get('guest_pharmacy_id');
        if (!$pharmacyId || $order->pharmacy_id != $pharmacyId) {
            return redirect()->route('guest.catalog');
        }

        $order->load(['items', 'pharmacy', 'unlockedRewardTier']);
        $milestone = $this->gatheringService->getPharmacyMilestone($order->pharmacy);

        return Inertia::render('Guest/Success', [
            'order' => $order,
            'milestone' => $milestone,
            'pharmacy' => $milestone['pharmacy'],
        ]);
    }
}
