<?php

namespace WebPag\Laravel;

use Illuminate\Support\ServiceProvider;
use WebPag\Configuration;
use WebPag\Environment;
use WebPag\WebPag;

class WebPagServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/webpag.php', 'webpag');

        $this->app->singleton(WebPag::class, function ($app) {
            $configArray = $app['config']['webpag'] ?? [];

            $configuration = Environment::fromArray($configArray)->toConfiguration();

            return new WebPag($configuration);
        });

        $this->app->alias(WebPag::class, 'webpag');
    }

    /**
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/webpag.php' => config_path('webpag.php'),
            ], 'webpag-config');
        }
    }
}
