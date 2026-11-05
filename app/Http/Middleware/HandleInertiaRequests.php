<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
                'env' => app()->environment(),
                'is_production' => app()->isProduction(),
            ],
            'auth' => [
                'user' => $request->user(),
            ],
            'guestPharmacyId' => $request->session()->get('guest_pharmacy_id'),
            'flash' => [
                'conflict' => $request->session()->get('conflict'),
                'conflictData' => $request->session()->get('conflictData'),
                'sessionTerminated' => $request->session()->get('sessionTerminated'),
                'terminatedMessage' => $request->session()->get('terminatedMessage'),
                'notFound' => $request->session()->get('notFound'),
                'searchedQuery' => $request->session()->get('searchedQuery'),
                'suggestions' => $request->session()->get('suggestions'),
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
