<?php

namespace Pbmengine\Stream\Tests;

use Pbmengine\Stream\Builder\Interval;
use Pbmengine\Stream\Builder\TimeFrame;

class QueryTest extends TestCase
{
    public function test_query_test()
    {
        $response = stream('logins')->query()
            ->where('itemPrice', '>', 100)
            ->where('itemId', '=', 200)
            ->orWhere('itemId', '=', 300)
            ->timeFrame(TimeFrame::PREV_MONTHS, 4)
            ->interval(Interval::DAILY)
            ->groupBy(['a', 'b'])
            ->distinct()
            ->orderBy('a')
            ->orderBy('b', 'desc')
            ->forPage(2, 200)
            ->encodedQuery();

        dd(json_decode(base64_decode($response), 1));
    }
}
