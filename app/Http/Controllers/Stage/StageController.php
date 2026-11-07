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
        $leaderboardData = $this->gatheringService->getAllLeaderboards();

        $totalGmv = (float) Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalOrders = (int) Order::where('status', '!=', 'cancelled')->count();

        return Inertia::render('Stage/Leaderboard', [
            'leaderboard' => $leaderboardData['overall'],
            'leaderboards_by_manufacturer' => $leaderboardData['by_manufacturer'],
            'stats_by_manufacturer' => $leaderboardData['stats_by_manufacturer'],
            'manufacturers' => $leaderboardData['manufacturers'],
            'stats' => [
                'total_gmv' => $totalGmv,
                'total_orders' => $totalOrders,
            ],
            'event' => [
                'name' => $eventSetting->event_name,
                'venue_name' => $eventSetting->venue_name,
                'session_start_at' => $eventSetting->session_start_at,
                'session_end_at' => $eventSetting->session_end_at,
                'is_ordering_active' => $eventSetting->is_ordering_active,
                'is_ordering_open' => $eventSetting->isOrderingOpen(),
                'ordering_status' => $eventSetting->getOrderingStatus(),
                'is_leaderboard_visible' => $eventSetting->is_leaderboard_visible,
                'doorprize_announcement' => $eventSetting->doorprize_announcement,
                'running_ticker_text' => $eventSetting->running_ticker_text,
            ],
        ]);
    }

    public function data(): JsonResponse
    {
        $eventSetting = EventSetting::current();
        $leaderboardData = $this->gatheringService->getAllLeaderboards();

        $totalGmv = (float) Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalOrders = (int) Order::where('status', '!=', 'cancelled')->count();

        return response()->json([
            'leaderboard' => $leaderboardData['overall'],
            'leaderboards_by_manufacturer' => $leaderboardData['by_manufacturer'],
            'stats_by_manufacturer' => $leaderboardData['stats_by_manufacturer'],
            'manufacturers' => $leaderboardData['manufacturers'],
            'stats' => [
                'total_gmv' => $totalGmv,
                'total_orders' => $totalOrders,
            ],
            'event' => [
                'name' => $eventSetting->event_name,
                'venue_name' => $eventSetting->venue_name,
                'session_start_at' => $eventSetting->session_start_at,
                'session_end_at' => $eventSetting->session_end_at,
                'is_ordering_active' => $eventSetting->is_ordering_active,
                'is_ordering_open' => $eventSetting->isOrderingOpen(),
                'ordering_status' => $eventSetting->getOrderingStatus(),
                'is_leaderboard_visible' => $eventSetting->is_leaderboard_visible,
                'doorprize_announcement' => $eventSetting->doorprize_announcement,
                'running_ticker_text' => $eventSetting->running_ticker_text,
            ],
        ]);
    }
}
