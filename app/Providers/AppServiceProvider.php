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
        //
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
}
