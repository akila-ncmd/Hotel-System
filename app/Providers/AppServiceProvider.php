<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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
        // Money display goes through one helper so the currency is declared in
        // exactly one place (config/hotel.php) rather than as a literal '$'
        // scattered across Blade views, PDF templates and mail templates.
        Blade::directive('money', function ($expression) {
            return "<?php echo \App\Support\Money::format($expression); ?>";
        });

        // Same, without the currency symbol, for tables that carry the currency
        // in their column header.
        Blade::directive('moneyPlain', function ($expression) {
            return "<?php echo \App\Support\Money::plain($expression); ?>";
        });
    }
}
