<?php

namespace Pbmengine\Stream\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Pbmengine\Stream\Stream;
use Pbmengine\Stream\StreamServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        app()->singleton(Stream::class, function ($app) {
            return new Stream(
                url: config('pbm-stream.url'),
                project: config('pbm-stream.project'),
                accessKey: config('pbm-stream.access_key')
            );
        });

        app()->bind('pbm-stream', function($app) {
            return new Stream(
                url: config('pbm-stream.url'),
                project: config('pbm-stream.project'),
                accessKey: config('pbm-stream.access_key')
            );
        });
    }

    public function getPackageProviders($app)
    {
        return [StreamServiceProvider::class];
    }
}
