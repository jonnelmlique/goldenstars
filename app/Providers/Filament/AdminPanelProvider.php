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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app') // Change from 'app' to empty string
            ->login(\App\Filament\Pages\Auth\Login::class)
                // ->registration(\App\Filament\Pages\Auth\Register::class)
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
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
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
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('Profile')
                    ->icon('heroicon-o-user')
                    ->url(fn() => \App\Filament\Pages\Profile::getUrl())
                    ->group('Account Settings')
                    ->sort(1),
                \Filament\Navigation\NavigationItem::make('Change Password')
                    ->icon('heroicon-o-key')
                    ->url(fn() => \App\Filament\Pages\ChangePassword::getUrl())
                    ->group('Account Settings')
                    ->sort(2),
            ])
            ->navigationGroups([
                'Configuration',
                'Users',
                'Account Settings',
            ]);
    }
}
