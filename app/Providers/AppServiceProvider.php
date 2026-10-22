<?php

namespace App\Providers;

use App\Models\User;
use App\Services\AppSettingService;
use App\Services\SidebarService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AppSettingService::class, function () {
            return new AppSettingService();
        });

        $this->app->singleton(SidebarService::class, function () {
            return new SidebarService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Permission Gate check using s_group_permissions
        Gate::before(function (User $user, string $ability) {
            if (in_array((int)$user->group_id, [1, 2])) {
                return true;
            }
            if ($user->hasPermission($ability)) {
                return true;
            }
        });

        // Share common view variables to all views
        View::composer('*', function ($view) {
            $settingService = app(AppSettingService::class);
            $sidebarService = app(SidebarService::class);
            $currentUser = auth()->user();

            $themeConfig = $settingService->getThemeConfig();
            $siteName = $settingService->get('site_name', 'KTKBM Apps');
            $logoSm = $settingService->get('site_logo_sm', 'assets/images/logo-sm.png');

            $view->with([
                'site_name' => $siteName,
                'logo_sm' => $logoSm,
                'theme_config' => $themeConfig,
                'user' => $currentUser,
                'info_karyawan' => $currentUser,
                'sidebar' => $currentUser ? $sidebarService->getForUser($currentUser) : [],
                'side_active' => request()->path(),
                'csrf_name' => env('CSRF_NAME', 'tkbm_csrf'),
                'csrf_token' => csrf_token(),
                'type_auth' => 1,
                'meta_description' => $themeConfig['meta_description'] ?? 'KTKBM Apps',
                'meta_keywords' => $themeConfig['meta_keywords'] ?? 'TKBM',
                'meta_author' => $themeConfig['meta_author'] ?? 'Bangchan',
                'meta_robots_content' => $themeConfig['meta_robots_content'] ?? 'noindex,nofollow',
            ]);
        });
    }
}
