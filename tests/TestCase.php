<?php

declare(strict_types=1);

namespace Pbmengine\Stream\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Pbmengine\Stream\StreamServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [StreamServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('pbm-stream', [
            'url' => 'https://stream.test/api',
            'project' => 'project-1',
            'access_key' => 'secret-key',
        ]);
    }
}
