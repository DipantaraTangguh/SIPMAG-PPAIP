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
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('SIPMAG')
            // Logo mengikuti latar tempatnya dirender: versi putih di sidebar
            // yang maroon, versi asli di halaman login yang berlatar terang --
            // di sana wordmark putih tidak akan terlihat sama sekali.
            ->brandLogo(fn (): string => Auth::check()
                ? asset('assets/images/logo-ubakrie-putih.png')
                : asset('assets/images/logo-ubakrie.png'))
            ->brandLogoHeight('2.5rem')
            ->login()
            ->colors([
                'primary' => Color::hex('#682828'),
            ])
            ->font('Plus Jakarta Sans')
            // Satu tampilan saja. Panel ini memakai warna institusi, dan mode
            // gelap membuat maroon-nya berkompromi dengan latar abu Filament.
            ->darkMode(false)
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): View => view('filament.brand-theme'))
            // Sidebar bisa diciutkan supaya tabel yang lebar -- daftar mahasiswa
            // punya sampai sepuluh kolom -- dapat ruang tanpa perlu menggeser
            // ke samping.
            ->sidebarCollapsibleOnDesktop()
            ->favicon(asset('assets/images/favicon.ico'))
            // Resource Filament dipisah per role, jadi subfolder ikut discan.
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
