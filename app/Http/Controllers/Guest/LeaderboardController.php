<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\EventSetting;
use App\Models\Pharmacy;
use App\Services\GatheringService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function __construct(
        protected GatheringService $gatheringService
    ) {}

    public function index(Request $request): Response
    {
        $pharmacyId = $request->session()->get('guest_pharmacy_id');
        $currentPharmacy = $pharmacyId ? Pharmacy::find($pharmacyId) : null;
        $tokenInSession = $request->session()->get('guest_device_token');

        if ($currentPharmacy && $currentPharmacy->device_session_token && $currentPharmacy->device_session_token !== $tokenInSession) {
            $request->session()->forget(['guest_pharmacy_id', 'guest_device_token']);
            $currentPharmacy = null;
        }

        $leaderboardData = $this->gatheringService->getAllLeaderboards();
        $eventSetting = EventSetting::current();

        $currentMilestone = null;
        $myStandings = null;

        if ($currentPharmacy) {
            $currentMilestone = $this->gatheringService->getPharmacyMilestone($currentPharmacy);

            $overallEntry = $leaderboardData['overall']->firstWhere('id', $currentPharmacy->id);

            $manufacturerStandings = [];
            foreach ($leaderboardData['manufacturers'] as $m) {
                $mId = (int) $m->id;
                $mLeaderboard = $leaderboardData['by_manufacturer'][$mId] ?? collect();
                $mEntry = $mLeaderboard->firstWhere('id', $currentPharmacy->id);

                $manufacturerStandings[] = [
                    'id' => $mId,
                    'name' => $m->name,
                    'slug' => $m->slug,
                    'rank' => $mEntry ? $mEntry['rank'] : null,
                    'total_spending' => $mEntry ? (float) $mEntry['total_spending'] : 0.0,
                    'has_spending' => $mEntry ? ($mEntry['total_spending'] > 0) : false,
                ];
            }

            $myStandings = [
                'overall' => [
                    'rank' => $overallEntry ? $overallEntry['rank'] : null,
                    'total_spending' => $overallEntry ? (float) $overallEntry['total_spending'] : 0.0,
                ],
                'manufacturers' => $manufacturerStandings,
            ];
        }

        $defaultTab = $request->query('manufacturer', 'all');

        return Inertia::render('Guest/Leaderboard', [
            'leaderboard' => $leaderboardData['overall'],
            'leaderboards_by_manufacturer' => $leaderboardData['by_manufacturer'],
            'manufacturers' => $leaderboardData['manufacturers'],
            'stats_by_manufacturer' => $leaderboardData['stats_by_manufacturer'],
            'initialTab' => $defaultTab,
            'currentPharmacy' => $currentPharmacy,
            'currentMilestone' => $currentMilestone,
            'myStandings' => $myStandings,
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
            ],
        ]);
    }
}
