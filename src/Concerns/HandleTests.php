<?php

declare(strict_types=1);

namespace Pbmengine\Stream\Concerns;

use Illuminate\Http\Client\Response;

trait HandleTests
{
    /**
     * @param array<string, mixed> $input
     */
    public function testEvent(array $input): Response
    {
        return $this
            ->client()
            ->post('/tests/events', $input);
    }
}
