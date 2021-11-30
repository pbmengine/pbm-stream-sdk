<?php

namespace Pbmengine\Stream;

use Illuminate\Support\ServiceProvider;

class StreamServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('pbm-stream.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'pbm-stream');

        $this->app->singleton(Stream::class, function ($app) {
            return new Stream(
                url: $app['config']['pbm-stream']['url'],
                project: $app['config']['pbm-stream']['project'],
                accessKey: $app['config']['pbm-stream']['access_key']
            );
        });
    }
}
