<?php

namespace App\Http\Controllers\Stage;

use App\Http\Controllers\Controller;
use App\Models\EventSetting;
use App\Models\Order;
use App\Services\GatheringService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class StageController extends Controller
{
    public function __construct(
        protected GatheringService $gatheringService
    ) {}

    public function index(): Response
    {
        $eventSetting = EventSetting::current();
        $leaderboard = $this->gatheringService->getLeaderboard();

        $totalGmv = (float) Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalOrders = (int) Order::where('status', '!=', 'cancelled')->count();

        return Inertia::render('Stage/Leaderboard', [
            'leaderboard' => $leaderboard,
            'stats' => [
                'total_gmv' => $totalGmv,
                'total_orders' => $totalOrders,
            ],
            'event' => [
                'name' => $eventSetting->event_name,
                'session_end_at' => $eventSetting->session_end_at,
                'is_ordering_active' => $eventSetting->is_ordering_active,
                'is_leaderboard_visible' => $eventSetting->is_leaderboard_visible,
                'doorprize_announcement' => $eventSetting->doorprize_announcement,
                'running_ticker_text' => $eventSetting->running_ticker_text,
            ],
        ]);
    }

    public function data(): JsonResponse
    {
        $eventSetting = EventSetting::current();
        $leaderboard = $this->gatheringService->getLeaderboard();

        $totalGmv = (float) Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalOrders = (int) Order::where('status', '!=', 'cancelled')->count();

        return response()->json([
            'leaderboard' => $leaderboard,
            'stats' => [
                'total_gmv' => $totalGmv,
                'total_orders' => $totalOrders,
            ],
            'event' => [
                'name' => $eventSetting->event_name,
                'session_end_at' => $eventSetting->session_end_at,
                'is_ordering_active' => $eventSetting->is_ordering_active,
                'is_leaderboard_visible' => $eventSetting->is_leaderboard_visible,
                'doorprize_announcement' => $eventSetting->doorprize_announcement,
                'running_ticker_text' => $eventSetting->running_ticker_text,
            ],
        ]);
    }
}
