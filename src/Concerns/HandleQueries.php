<?php

namespace Pbmengine\Stream\Concerns;

use Pbmengine\Stream\Builder\Query;

trait HandleQueries
{
    public function query(): Query
    {
        return new Query($this);
    }
}
