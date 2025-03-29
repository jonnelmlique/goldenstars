<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\MenuItem;
use App\Filament\Pages\Profile;
use App\Filament\Pages\ChangePassword;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Awcodes\LightSwitch\Enums\Alignment;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Pages\CompleteProfile::class,
                \App\Filament\Pages\Profile::class,
                \App\Filament\Pages\ChangePassword::class,
                \App\Filament\Pages\WarehouseView::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\TicketStatsOverview::class,
                \App\Filament\Widgets\TicketsChart::class,
                \App\Filament\Widgets\TicketsByStatusChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\EnsureProfileIsComplete::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->userMenuItems(items: [
                MenuItem::make()
                    ->label('Profile')
                    ->icon('heroicon-o-user')
                    ->url(fn() => Profile::getUrl()),
                MenuItem::make()
                    ->label('Change Password')
                    ->icon('heroicon-o-key')
                    ->url(fn() => ChangePassword::getUrl()),
            ])
            ->navigationItems([
                // Remove the Profile and Change Password navigation items
            ])
            ->navigationGroups([
                'IT',
                'Warehouse',
                'Account Settings',
            ])
            ->resources([
                \App\Filament\Resources\WarehouseLocationResource::class,
                \App\Filament\Resources\WarehouseShelfResource::class,
                \App\Filament\Resources\WarehouseInventoryResource::class,
            ])
            ->renderHook(
                'panels::scripts.after',
                fn() => '<script src="' . asset('build/assets/app.js') . '"></script>'
            )
            ->plugins([
                LightSwitchPlugin::make(),
            ]);
    }
}
