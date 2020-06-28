<?php

namespace Beeyev\LaravelFlightAwareClient;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class FlightAwareServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/flightaware.php' => config_path('flightaware.php'),
        ]);
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('flightaware', function ($app) {
            return new FlightAware($app['config']['flightaware']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['flightaware'];
    }
}
