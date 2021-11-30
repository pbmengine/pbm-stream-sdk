<?php

namespace Pbmengine\Stream\Concerns;

use Illuminate\Http\Client\Response;

trait HandleTests
{
    public function testEvent(array $input): Response
    {
        return $this
            ->client()
            ->post('/tests/events', $input);
    }
}
