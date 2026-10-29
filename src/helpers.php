<?php

declare(strict_types=1);

if (!function_exists('stream')) {
    function stream(string $collection = ''): \Pbmengine\Stream\Stream {
        return !empty($collection)
            ? app(\Pbmengine\Stream\Stream::class)->setCollection($collection)
            : app(\Pbmengine\Stream\Stream::class);
    }
}
