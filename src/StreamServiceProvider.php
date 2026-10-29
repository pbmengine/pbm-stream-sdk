<?php

declare(strict_types=1);

namespace Pbmengine\Stream;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class StreamServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
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
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'pbm-stream');

        $this->app->singleton(Stream::class, function (Application $app): Stream {
            $config = $app['config']['pbm-stream'];

            return new Stream(
                (string) $config['url'],
                (string) $config['project'],
                (string) $config['access_key']
            );
        });
    }
}
