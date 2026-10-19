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

        // Dynamically bind database GymSettings to config('gym.*')
        try {
            if (!app()->runningInConsole() && \Illuminate\Support\Facades\Schema::hasTable('gym_settings')) {
                $dbSettings = \Illuminate\Support\Facades\Cache::remember('gym_system_settings', 3600, function () {
                    return \App\Models\GymSetting::pluck('value', 'key')->toArray();
                });

                if (!empty($dbSettings)) {
                    $mapped = [
                        'name' => $dbSettings['gym_name'] ?? null,
                        'tagline' => $dbSettings['gym_tagline'] ?? null,
                        'phone' => $dbSettings['gym_phone'] ?? null,
                        'email' => $dbSettings['gym_email'] ?? null,
                        'address' => $dbSettings['gym_address'] ?? null,
                        'currency_symbol' => $dbSettings['currency_symbol'] ?? null,
                        'currency_code' => $dbSettings['currency_code'] ?? null,
                        'tax_rate_percentage' => isset($dbSettings['tax_rate_percentage']) ? (float)$dbSettings['tax_rate_percentage'] : null,
                        'allow_grace_period_days' => isset($dbSettings['grace_period_days']) ? (int)$dbSettings['grace_period_days'] : null,
                    ];

                    foreach (array_filter($mapped, fn($v) => $v !== null) as $cfgKey => $cfgVal) {
                        config(["gym.{$cfgKey}" => $cfgVal]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Graceful fallback to default config when DB is offline or migrating
        }

        // Custom Blade directive for formatting currency
        Blade::directive('currency', function ($expression) {
            return "<?php echo config('gym.currency_symbol', '$') . number_format((float)($expression), 2); ?>";
        });
    }
}
