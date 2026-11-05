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

        $leaderboard = $this->gatheringService->getLeaderboard();
        $eventSetting = EventSetting::current();

        $currentMilestone = null;
        if ($currentPharmacy) {
            $currentMilestone = $this->gatheringService->getPharmacyMilestone($currentPharmacy);
        }

        return Inertia::render('Guest/Leaderboard', [
            'leaderboard' => $leaderboard,
            'currentPharmacy' => $currentPharmacy,
            'currentMilestone' => $currentMilestone,
            'event' => [
                'name' => $eventSetting->event_name,
                'session_end_at' => $eventSetting->session_end_at,
                'is_ordering_active' => $eventSetting->is_ordering_active,
                'is_leaderboard_visible' => $eventSetting->is_leaderboard_visible,
                'doorprize_announcement' => $eventSetting->doorprize_announcement,
            ],
        ]);
    }
}
