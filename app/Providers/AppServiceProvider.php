<?php

namespace App\Providers;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /** Nothing to register: the app uses framework defaults. */
    public function register(): void
    {
        //
    }

    /** Bootstrap 5 pagination markup and the shared layout data (view composers). */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Categories for the storefront header + footer.
        View::composer('layouts.app', function (ViewContract $view) {
            $view->with('navCategories', $this->safely(
                fn () => Category::orderBy('name')->get(['id', 'name', 'slug']),
                collect()
            ));
        });

        // Pending-orders badge in the admin sidebar.
        View::composer('layouts.admin', function (ViewContract $view) {
            $view->with('pendingOrdersCount', $this->safely(
                fn () => Order::where('status', OrderStatus::Pending)->count(),
                0
            ));
        });
    }

    /**
     * Layouts must still render (e.g. the 404 page) when the database is not
     * reachable or not migrated yet, so lookups fall back to a default.
     */
    private function safely(callable $callback, Collection|int $default): Collection|int
    {
        try {
            return $callback();
        } catch (Throwable) {
            return $default;
        }
    }
}
