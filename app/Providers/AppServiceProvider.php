<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Offer;
use App\Support\RoomCatalog;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('manage-marketing', fn (User $user): bool => $user->is_marketing_admin === true);

        // The book-direct offer ($promo: active, discount, deadline, countdown data),
        // worked out once per request and shared with every public template.
        $promo = null;
        View::composer(['layouts.app', 'partials.*', 'pages.*'], function ($view) use (&$promo) {
            $promo ??= Offer::forViews(RoomCatalog::all());
            $view->with('promo', $promo);
        });
    }
}
