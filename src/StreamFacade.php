<?php

namespace Pbmengine\Stream;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Pbmengine\Stream\Stream
 */
class StreamFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'pbm-stream';
    }
}
