<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\DashboardOverviewWidget;
use App\Filament\Widgets\ManufacturerRevenueOverviewWidget;
use App\Filament\Widgets\RecentOrdersOverviewWidget;
use App\Filament\Widgets\TopProductsWidget;
use App\Filament\Widgets\TopSpendersWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('SMS Gathering Admin')
            ->colors([
                'primary' => [
                    50 => '#E3F2FD',
                    100 => '#BBDEFB',
                    200 => '#90CAF9',
                    300 => '#64B5F6',
                    400 => '#42A5F5',
                    500 => '#2196F3',
                    600 => '#1565C0',
                    700 => '#0D47A1',
                    800 => '#00468C',
                    900 => '#0B1C30',
                    950 => '#090D16',
                ],
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                DashboardOverviewWidget::class,
                TopSpendersWidget::class,
                TopProductsWidget::class,
                ManufacturerRevenueOverviewWidget::class,
                RecentOrdersOverviewWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
