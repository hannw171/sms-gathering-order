<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\EventSetting;
use App\Models\Pharmacy;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(Request $request): Response|RedirectResponse
    {
        // If already logged in, redirect to catalog
        if ($request->session()->has('guest_pharmacy_id')) {
            $pharmacy = Pharmacy::find($request->session()->get('guest_pharmacy_id'));
            $tokenInSession = $request->session()->get('guest_device_token');

            if ($pharmacy) {
                if (! $pharmacy->device_session_token || $pharmacy->device_session_token === $tokenInSession) {
                    return redirect()->route('guest.catalog');
                }

                $request->session()->forget(['guest_pharmacy_id', 'guest_device_token']);

                return redirect()->route('guest.login')->with([
                    'sessionTerminated' => true,
                    'terminatedMessage' => 'Sesi Anda telah berakhir karena akun apotek ini baru saja dibuka di perangkat atau browser lain.',
                ]);
            }

            $request->session()->forget(['guest_pharmacy_id', 'guest_device_token']);
        }

        $eventSetting = EventSetting::current();

        return Inertia::render('Guest/Onboarding', [
            'event' => [
                'name' => $eventSetting->event_name,
                'venue_name' => $eventSetting->venue_name,
                'registration_schedule' => $eventSetting->registration_schedule,
                'registration_help_note' => $eventSetting->registration_help_note,
                'session_start_at' => $eventSetting->session_start_at,
                'session_end_at' => $eventSetting->session_end_at,
                'is_ordering_active' => $eventSetting->is_ordering_active,
                'is_ordering_open' => $eventSetting->isOrderingOpen(),
                'ordering_status' => $eventSetting->getOrderingStatus(),
            ],
            // Pre-seed all real pharmacies for instant client-side autocomplete
            'samplePharmacies' => Pharmacy::select('id', 'customer_code', 'name', 'city', 'owner_name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'customer_code' => ['required', 'string'],
        ]);

        $code = trim(strtoupper($request->customer_code));
        $pharmacy = Pharmacy::where('customer_code', $code)
            ->orWhere('name', 'like', '%'.$request->customer_code.'%')
            ->first();

        if (! $pharmacy) {
            $suggestions = Pharmacy::select('id', 'name', 'customer_code', 'city')
                ->limit(2)
                ->get();

            return back()->with([
                'notFound' => true,
                'searchedQuery' => $request->customer_code,
                'suggestions' => $suggestions,
            ])->withErrors([
                'customer_code' => 'Nama sarana tidak ditemukan. Pastikan nama sesuai undangan fisik atau hubungi PIC Sales meja Anda untuk aktivasi instan.',
            ]);
        }

        $currentToken = $request->session()->get('guest_device_token');

        // Check if device conflict exists
        if ($pharmacy->device_session_token && $pharmacy->device_session_token !== $currentToken && ! $request->boolean('force_takeover')) {
            return back()->with([
                'conflict' => true,
                'conflictData' => [
                    'pharmacy_id' => $pharmacy->id,
                    'pharmacy_name' => $pharmacy->name,
                    'customer_code' => $pharmacy->customer_code,
                    'previous_device' => $pharmacy->device_name ?: 'Chrome Mobile • Samsung S23',
                    'last_active_time' => $pharmacy->last_active_at ? Carbon::parse($pharmacy->last_active_at)->diffForHumans() : '2 menit lalu',
                ],
            ]);
        }

        // Determine friendly device name
        $ua = $request->userAgent() ?? '';
        $deviceName = 'Web Browser • Mobile Device';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
            $deviceName = 'Safari Mobile • iPhone';
        } elseif (str_contains($ua, 'Android')) {
            $deviceName = 'Chrome Mobile • Android';
        } elseif (str_contains($ua, 'Macintosh')) {
            $deviceName = 'Safari • macOS';
        } elseif (str_contains($ua, 'Windows')) {
            $deviceName = 'Chrome • Windows';
        }

        $deviceToken = (string) Str::random(40);

        // Establish session
        $pharmacy->update([
            'device_session_token' => $deviceToken,
            'device_name' => $deviceName,
            'last_active_at' => now(),
            'is_checked_in' => true,
            'checked_in_at' => $pharmacy->checked_in_at ?? now(),
        ]);

        $request->session()->put('guest_pharmacy_id', $pharmacy->id);
        $request->session()->put('guest_device_token', $deviceToken);

        return redirect()->route('guest.catalog');
    }

    public function logout(Request $request)
    {
        if ($pharmacyId = $request->session()->get('guest_pharmacy_id')) {
            Pharmacy::where('id', $pharmacyId)->update([
                'device_session_token' => null,
            ]);
        }

        $request->session()->forget(['guest_pharmacy_id', 'guest_device_token']);

        return redirect()->route('guest.login');
    }
}
