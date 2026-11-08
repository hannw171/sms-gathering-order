<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\EventSetting;
use App\Models\Manufacturer;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Services\GatheringService;
use App\Services\RewardCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __construct(
        protected GatheringService $gatheringService,
        protected RewardCalculationService $rewardService
    ) {}

    public function index(Request $request): Response|RedirectResponse
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
            $request->session()->forget('guest_pharmacy_id');

            return redirect()->route('guest.login');
        }

        $manufacturers = Manufacturer::where('is_active', true)
            ->get()
            ->sort(function ($a, $b) {
                $aOpen = $a->isOrderingOpen() ? 1 : 0;
                $bOpen = $b->isOrderingOpen() ? 1 : 0;

                if ($aOpen !== $bOpen) {
                    return $bOpen <=> $aOpen;
                }

                return ($a->sort_order ?? 0) <=> ($b->sort_order ?? 0) ?: strcmp($a->name, $b->name);
            })
            ->values();

        $query = Product::with('manufacturer')
            ->where('is_active', true);

        if ($request->filled('manufacturer')) {
            $slug = $request->manufacturer;
            if ($slug !== 'semua') {
                $query->whereHas('manufacturer', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name', 'asc')->get();

        // Milestone and Reward Tracker Data
        $milestone = $this->gatheringService->getPharmacyMilestone($pharmacy);
        $currentRewards = $this->rewardService->calculateForPharmacy($pharmacy);
        $ordersCount = $pharmacy->orders->count();
        $eventSetting = EventSetting::current();

        return Inertia::render('Guest/Catalog', [
            'pharmacy' => $milestone['pharmacy'],
            'milestone' => $milestone,
            'currentRewards' => $currentRewards,
            'ordersCount' => $ordersCount,
            'manufacturers' => $manufacturers,
            'products' => $products,
            'selectedManufacturer' => $request->input('manufacturer', 'semua'),
            'searchQuery' => $request->input('search', ''),
            'event' => [
                'name' => $eventSetting->event_name,
                'venue_name' => $eventSetting->venue_name,
                'session_start_at' => $eventSetting->session_start_at,
                'session_end_at' => $eventSetting->session_end_at,
                'is_ordering_active' => $eventSetting->is_ordering_active,
                'is_ordering_open' => $eventSetting->isOrderingOpen(),
                'ordering_status' => $eventSetting->getOrderingStatus(),
                'is_leaderboard_visible' => (bool) $eventSetting->is_leaderboard_visible,
                'doorprize_announcement' => $eventSetting->doorprize_announcement,
                'running_ticker_text' => $eventSetting->running_ticker_text,
            ],
        ]);
    }
}
