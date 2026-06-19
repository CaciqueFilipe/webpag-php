<?php

namespace WebPag\Laravel;

use Illuminate\Support\ServiceProvider;
use WebPag\Configuration;
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
            $config = $app['config']['webpag'];

            return new WebPag(new Configuration(
                $config['api_token'],
                isset($config['base_url']) ? $config['base_url'] : null,
                isset($config['timeout']) ? (int) $config['timeout'] : 30
            ));
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
