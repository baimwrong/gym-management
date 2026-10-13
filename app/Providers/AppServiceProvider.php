<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;

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
        // Use Bootstrap 5 pagination
        Paginator::useBootstrapFive();

        // Custom Blade directive for formatting currency
        Blade::directive('currency', function ($expression) {
            return "<?php echo config('gym.currency_symbol', '$') . number_format((float)($expression), 2); ?>";
        });
    }
}
