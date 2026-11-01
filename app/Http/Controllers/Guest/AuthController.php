<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\EventSetting;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(Request $request): Response
    {
        // If already logged in, redirect to catalog
        if ($request->session()->has('guest_pharmacy_id')) {
            return Inertia::render('Guest/Catalog');
        }

        $eventSetting = EventSetting::current();

        return Inertia::render('Guest/Onboarding', [
            'event' => [
                'name' => $eventSetting->event_name,
                'session_end_at' => $eventSetting->session_end_at,
                'is_ordering_active' => $eventSetting->is_ordering_active,
            ],
            // Demo quick switch apotek for convenience during event testing
            'samplePharmacies' => Pharmacy::select('customer_code', 'name', 'table_number')->limit(5)->get(),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'customer_code' => ['required', 'string'],
        ]);

        $code = trim(strtoupper($request->customer_code));
        $pharmacy = Pharmacy::where('customer_code', $code)
            ->orWhere('name', 'like', '%' . $request->customer_code . '%')
            ->first();

        if (!$pharmacy) {
            return back()->withErrors([
                'customer_code' => 'Data apotek tidak ditemukan. Silakan periksa kembali kode undangan atau tanyakan ke panitia meja.',
            ]);
        }

        $currentSessionToken = $request->session()->getId();

        // Check if device conflict exists
        if ($pharmacy->device_session_token && $pharmacy->device_session_token !== $currentSessionToken && !$request->boolean('force_takeover')) {
            return back()->with([
                'conflict' => true,
                'pharmacy' => [
                    'id' => $pharmacy->id,
                    'name' => $pharmacy->name,
                    'customer_code' => $pharmacy->customer_code,
                    'table_number' => $pharmacy->table_number,
                ],
            ]);
        }

        // Establish session
        $pharmacy->update([
            'device_session_token' => $currentSessionToken,
            'is_checked_in' => true,
            'checked_in_at' => $pharmacy->checked_in_at ?? now(),
        ]);

        $request->session()->put('guest_pharmacy_id', $pharmacy->id);

        return redirect()->route('guest.catalog');
    }

    public function logout(Request $request)
    {
        if ($pharmacyId = $request->session()->get('guest_pharmacy_id')) {
            Pharmacy::where('id', $pharmacyId)->update([
                'device_session_token' => null,
            ]);
        }

        $request->session()->forget('guest_pharmacy_id');
        return redirect()->route('guest.login');
    }
}
