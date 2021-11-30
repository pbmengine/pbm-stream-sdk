<?php

namespace Pbmengine\Stream\Concerns;

use Illuminate\Http\Client\Response;

trait HandleStatus
{
    public function status(): Response
    {
        return $this
            ->client()
            ->get('/status');
    }
}
