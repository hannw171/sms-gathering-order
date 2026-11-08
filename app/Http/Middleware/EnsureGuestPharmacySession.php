<?php

namespace App\Http\Middleware;

use App\Models\Pharmacy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuestPharmacySession
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $pharmacyId = $request->session()->get('guest_pharmacy_id');

        if (! $pharmacyId) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('guest.login');
        }

        $pharmacy = Pharmacy::find($pharmacyId);

        if (! $pharmacy) {
            $request->session()->forget('guest_pharmacy_id');

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Pharmacy not found.'], 401);
            }

            return redirect()->route('guest.login');
        }

        $tokenInSession = $request->session()->get('guest_device_token');
        $tokenInDb = $pharmacy->device_session_token;

        if (! $tokenInDb || $tokenInSession === $tokenInDb) {
            $request->attributes->set('guest_pharmacy', $pharmacy);
        }

        // Check if device session was taken over by another device
        if ($tokenInDb && $tokenInSession !== $tokenInDb) {
            $request->session()->forget(['guest_pharmacy_id', 'guest_device_token']);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi Anda telah dialihkan atau dibuka pada perangkat lain.',
                ], 401);
            }

            return redirect()->route('guest.login')->with([
                'sessionTerminated' => true,
                'terminatedMessage' => 'Sesi Anda telah berakhir karena akun apotek ini baru saja dibuka di perangkat atau browser lain.',
            ]);
        }

        return $next($request);
    }
}
