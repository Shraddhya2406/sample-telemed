<?php

namespace App\Providers;

use App\Models\AppNotification;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->disableBrokenLocalProxy();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        View::composer(['layouts.patient', 'doctor.layout'], function ($view) {
            $user = auth()->user();
            $notificationsReady = Schema::hasTable('app_notifications');

            $view->with([
                'notificationUnreadCount' => $user && $notificationsReady
                    ? AppNotification::where('user_id', $user->id)->whereNull('read_at')->count()
                    : 0,
                'headerNotifications' => $user && $notificationsReady
                    ? AppNotification::where('user_id', $user->id)->latest()->limit(6)->get()
                    : collect(),
            ]);
        });
    }

    private function disableBrokenLocalProxy(): void
    {
        foreach (['HTTP_PROXY', 'HTTPS_PROXY', 'ALL_PROXY', 'http_proxy', 'https_proxy', 'all_proxy'] as $name) {
            $value = getenv($name);

            if ($value !== false && str_contains($value, '127.0.0.1:9')) {
                putenv($name);
                unset($_ENV[$name], $_SERVER[$name]);
            }
        }
    }
}
