<?php
/**
 *
 * @copyright(c) 2019
 * @created by  shelwin
 * @package confhub-sdk
 * @version LumenServiceProvider: LumenServiceProvider.php 2019-07-01 16:23 $
 */

namespace Xiaoe\ClockHub;

use Illuminate\Support\ServiceProvider;

class ClockHubServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/confhub.php' => base_path('config/confhub.php'),
        ], 'confhub');
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton('confhub', function ($app) {
            return new ClockHub(config('confhub', []), null, null);
        });
        $this->app->alias('confhub', ClockHub::class);
    }

    /**
     * Merge configurations.
     */
    protected function mergeConfigs()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/confhub.php', 'confhub');
    }
}