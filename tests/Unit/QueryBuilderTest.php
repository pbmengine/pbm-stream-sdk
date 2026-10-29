<?php

declare(strict_types=1);

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Pbmengine\Stream\Builder\TimeFrame;

afterEach(fn () => Carbon::setTestNow());

it('builds the query payload from fluent calls', function () {
    $payload = stream('logins')->query()
        ->where('itemPrice', '>', 100)
        ->orWhere('itemId', '=', 300)
        ->whereIn('country', collect(['DE', 'AT']))
        ->groupBy(['a', 'b'], 'c')
        ->orderBy('a')
        ->orderByDesc('b')
        ->forPage(2, 200)
        ->explain();

    expect($payload)
        ->wheres->toHaveCount(3)
        ->and($payload['wheres'][1]['boolean'])->toBe('or')
        ->and($payload['wheres'][2]['values'])->toBe(['DE', 'AT'])
        ->and($payload['groups'])->toBe(['a', 'b', 'c'])
        ->and($payload['orders'][1])->toBe(['column' => 'b', 'direction' => 'desc'])
        ->and($payload['offset'])->toBe(200)
        ->and($payload['limit'])->toBe(200);
});

it('round-trips the encoded query', function () {
    $query = stream('logins')->query()->where('a', '=', 1);

    expect(json_decode(base64_decode($query->encodedQuery()), true))->toBe($query->explain());
});

it('rejects invalid order directions', function () {
    stream('logins')->query()->orderBy('a', 'sideways');
})->throws(InvalidArgumentException::class);

it('accepts carbon instances including immutable ones as time frame', function () {
    $payload = stream('logins')->query()
        ->timeFrame(CarbonImmutable::parse('2026-01-01'), Carbon::parse('2026-02-01'))
        ->explain();

    expect($payload['timeFrame'])->toBe(['2026-01-01T00:00:00+00:00', '2026-02-01T00:00:00+00:00']);
});

it('rejects a time frame whose start is after its end', function () {
    stream('logins')->query()->timeFrame('2026-02-01', '2026-01-01');
})->throws(InvalidArgumentException::class);

it('resolves relative previous time frames', function () {
    Carbon::setTestNow('2026-10-29 12:34:56');

    expect(TimeFrame::byKey(TimeFrame::PREV_DAYS, 3))
        ->toBe(['2026-10-26T00:00:00+00:00', '2026-10-28T23:59:59+00:00']);
});

it('resolves relative this time frames via the query builder', function () {
    Carbon::setTestNow('2026-10-29 12:34:56');

    expect(stream('logins')->query()->timeFrame(TimeFrame::THIS_HOURS, 2)->explain()['timeFrame'])
        ->toBe(['2026-10-29T10:00:00+00:00', '2026-10-29T12:34:56+00:00']);
});
