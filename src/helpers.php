<?php

if (!function_exists('stream')) {
    function stream(string $collection = ''): \Pbmengine\Stream\Stream {
        return !empty($collection)
            ? app('pbm-stream')->setCollection($collection)
            : app('pbm-stream');
    }
}
