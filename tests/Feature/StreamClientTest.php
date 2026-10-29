<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Pbmengine\Stream\Facades\Stream as StreamFacade;
use Pbmengine\Stream\Stream;

beforeEach(fn () => Http::fake());

it('resolves the stream client as singleton from config', function () {
    expect(app(Stream::class))
        ->toBe(app(Stream::class))
        ->getProject()->toBe('project-1')
        ->and(StreamFacade::getFacadeRoot())->toBe(app(Stream::class));
});

it('sends the access key and json accept header', function () {
    stream('logins')->record(['userId' => 1]);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://stream.test/api/projects/project-1/collections/logins/events'
        && $request->method() === 'POST'
        && $request->header('Authorization') === ['secret-key']
        && $request->header('Accept') === ['application/json']
        && $request['userId'] === 1);
});

it('percent-encodes path segments so ids cannot traverse to other endpoints', function () {
    stream('logins')->updateEvents('../../../admin', 'a?b#c', ['x' => 1]);

    Http::assertSent(fn (Request $request) => $request->url()
        === 'https://stream.test/api/projects/project-1/collections/logins/events/condition/..%2F..%2F..%2Fadmin/a%3Fb%23c');
});

it('percent-encodes the event id on delete', function () {
    stream('logins')->deleteEvent('1/../../indexes/foo');

    Http::assertSent(fn (Request $request) => $request->method() === 'DELETE'
        && $request->url() === 'https://stream.test/api/projects/project-1/collections/logins/events/1%2F..%2F..%2Findexes%2Ffoo');
});

it('percent-encodes the collection in query requests', function () {
    stream('a/b')->query()->where('x', '=', 1)->get();

    Http::assertSent(fn (Request $request) => $request->url() === 'https://stream.test/api/projects/project-1/collections/a%2Fb/query'
        && $request['wheres'][0]['column'] === 'x');
});

it('throws a descriptive exception when no collection is set', function () {
    (new Stream('https://stream.test/api', 'project-1', 'secret-key'))->record([]);
})->throws(LogicException::class, 'No collection set');

it('sends no request when no collection is set', function () {
    rescue(fn () => (new Stream('https://stream.test/api', 'project-1', 'secret-key'))->deleteEvent('1'), report: false);

    Http::assertNothingSent();
});

it('calls project level endpoints without a collection', function () {
    app(Stream::class)->collections();
    app(Stream::class)->status();

    Http::assertSent(fn (Request $request) => $request->url() === 'https://stream.test/api/projects/project-1/collections');
    Http::assertSent(fn (Request $request) => $request->url() === 'https://stream.test/api/status');
});
