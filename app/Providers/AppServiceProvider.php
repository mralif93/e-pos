<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use App\Domains\Sales\Events\SaleCompleted;
use App\Domains\Sales\Listeners\RewardLoyaltyPoints;
use App\Domains\Sales\Listeners\SubmitToLHDNEInvoice;

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
        Paginator::defaultView('vendor.pagination.tailwind');

        // Event Registration
        Event::listen(
            SaleCompleted::class,
            [
                RewardLoyaltyPoints::class,
                SubmitToLHDNEInvoice::class,
            ]
        );

        Gate::define('access-pos', function (User $user) {
            return in_array($user->role, ['Cashier', 'Manager', 'Admin', 'Super Admin']);
        });

        Gate::define('access-admin', function (User $user) {
            return in_array($user->role, ['Admin', 'Super Admin']);
        });
    }
}
