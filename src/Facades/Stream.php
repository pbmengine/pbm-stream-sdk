<?php

declare(strict_types=1);

namespace Pbmengine\Stream\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Pbmengine\Stream\Stream
 */
class Stream extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Pbmengine\Stream\Stream::class;
    }
}
