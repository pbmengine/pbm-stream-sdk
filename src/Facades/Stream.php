<?php

namespace Pbmengine\Stream\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Pbmengine\Stream\Stream
 */
class Stream extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Pbmengine\Stream\Stream::class;
    }
}
